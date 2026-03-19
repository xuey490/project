/**
 * Stock Analysis SDK
 *
 * Architecture:
 *   1. Yahoo Finance (yahoo-finance2) → precise OHLCV historical data
 *   2. Local calculation → MA/MACD/RSI/100-point scoring
 *   3. Tavily (@tavily/core) → news/sentiment search
 *   4. Hunyuan Chat (hunyuan-chat-integrator) → intelligent analysis report generation
 *
 * Based on the analysis methodology from daily_stock_analysis project.
 *
 * Dependencies:
 *   - yahoo-finance2                    (historical + realtime market data)
 *   - @tavily/core                      (news search)
 *   - tencentcloud-sdk-nodejs-hunyuan   (LLM analysis via hunyuan-chat-integrator)
 */

import YahooFinance from 'yahoo-finance2';
const yahooFinance = new YahooFinance();
import { tavily, TavilyClient } from '@tavily/core';
import { createClient as createHunyuanClient, HunyuanClient } from './hunyuan-chat';

// ============================================================
// Types & Interfaces
// ============================================================

export interface StockAnalysisConfig {
  tavilyApiKey?: string;
}

export type TrendStatus =
  | 'STRONG_BULL' | 'BULL' | 'WEAK_BULL'
  | 'CONSOLIDATION'
  | 'WEAK_BEAR' | 'BEAR' | 'STRONG_BEAR';

export type VolumeStatus =
  | 'HEAVY_VOLUME_UP' | 'HEAVY_VOLUME_DOWN'
  | 'SHRINK_VOLUME_UP' | 'SHRINK_VOLUME_DOWN'
  | 'NORMAL';

export type MACDStatus =
  | 'GOLDEN_CROSS_ZERO' | 'GOLDEN_CROSS'
  | 'BULLISH' | 'CROSSING_UP' | 'CROSSING_DOWN'
  | 'BEARISH' | 'DEATH_CROSS';

export type RSIStatus =
  | 'OVERBOUGHT' | 'STRONG_BUY' | 'NEUTRAL' | 'WEAK' | 'OVERSOLD';

export type BuySignal =
  | 'STRONG_BUY' | 'BUY' | 'HOLD' | 'WAIT' | 'SELL' | 'STRONG_SELL';

/** 纯均线排列状态（只看MA大小关系，与价格无关） */
export type MAArrangement = 'BULL' | 'BEAR' | 'MIXED';

/** Single OHLCV bar */
export interface OHLCVBar {
  date: string;
  open: number;
  high: number;
  low: number;
  close: number;
  volume: number;
}

/** Market data from Yahoo Finance */
export interface MarketData {
  stockCode: string;
  stockName: string;
  bars: OHLCVBar[];         // 完整历史 K 线
  price: number;            // 最新收盘价
  changePercent: number;    // 涨跌幅 %
  open: number;
  high: number;
  low: number;
  prevClose: number;
  volume: number;
  // 均线 (本地计算)
  ma5: number;
  ma10: number;
  ma20: number;
  ma60: number;
  // MACD (本地计算)
  macdDif: number;
  macdDea: number;
  macdBar: number;
  prevMacdDif: number;
  prevMacdDea: number;
  // RSI (本地计算)
  rsi6: number;
  rsi12: number;
  rsi24: number;
  // 量能 (本地计算)
  volumeRatio5d: number;    // 当日量 / 5日均量
  // 估值 (从 Yahoo quote)
  pe?: number;
  pb?: number;
  marketCap?: number;
}

export interface NewsItem {
  title: string;
  snippet: string;
  url: string;
  source: string;
  publishedDate?: string;
}

export interface TechnicalAnalysis {
  maArrangement: MAArrangement;  // 纯均线排列（只看MA大小关系）
  trendStatus: TrendStatus;
  maAlignment: string;
  trendStrength: number;
  biasMa5: number;
  biasMa10: number;
  biasMa20: number;
  volumeStatus: VolumeStatus;
  volumeTrend: string;
  volumeRatio5d: number;
  supportMa5: boolean;
  supportMa10: boolean;
  macdStatus: MACDStatus;
  macdSignal: string;
  rsiStatus: RSIStatus;
  rsiSignal: string;
  buySignal: BuySignal;
  signalScore: number;
  signalReasons: string[];
  riskFactors: string[];
}

export interface AnalysisResult {
  markdown: string;
  marketData: MarketData;
  news: NewsItem[];
  technicalAnalysis: TechnicalAnalysis;
}

// ============================================================
// Stock name map
// ============================================================

const STOCK_NAME_MAP: Record<string, string> = {
  '600519': '贵州茅台', '000001': '平安银行', '300750': '宁德时代',
  '002594': '比亚迪', '600036': '招商银行', '601318': '中国平安',
  '000858': '五粮液', '600276': '恒瑞医药', '601012': '隆基绿能',
  '002475': '立讯精密', '300059': '东方财富', '002415': '海康威视',
  '600900': '长江电力', '601166': '兴业银行', '600028': '中国石化',
  'AAPL': 'Apple', 'TSLA': 'Tesla', 'MSFT': 'Microsoft',
  'GOOGL': 'Google', 'AMZN': 'Amazon', 'NVDA': 'NVIDIA',
  'META': 'Meta', 'AMD': 'AMD', 'BABA': 'Alibaba',
  'PDD': 'PDD Holdings', 'JD': 'JD.com', 'BIDU': 'Baidu',
  '00700': '腾讯控股', '03690': '美团', '01810': '小米集团',
  '09988': '阿里巴巴-SW', '09618': '京东集团-SW',
};

// ============================================================
// Yahoo Finance code conversion (mirrors yfinance_fetcher.py)
// ============================================================

function detectMarket(code: string): 'A' | 'US' | 'HK' {
  if (/^[A-Z]{1,5}$/.test(code)) return 'US';
  if (/^\d{5}$/.test(code)) return 'HK';
  return 'A';
}

/**
 * Convert stock code to Yahoo Finance symbol.
 *
 * A-share Shanghai: 600519 -> 600519.SS
 * A-share Shenzhen: 000001 -> 000001.SZ
 * HK: 00700 -> 0700.HK
 * US: AAPL -> AAPL (as-is)
 */
function toYahooSymbol(code: string): string {
  const market = detectMarket(code);
  if (market === 'US') return code;
  if (market === 'HK') {
    const num = code.replace(/^0+/, '') || '0';
    return `${num.padStart(4, '0')}.HK`;
  }
  // A-share
  if (/^(600|601|603|688|51|52|56|58)/.test(code)) return `${code}.SS`;
  return `${code}.SZ`;
}

// ============================================================
// Technical indicator calculators (local, no external dep)
// ============================================================

/** Simple Moving Average over `period` bars */
function sma(closes: number[], period: number): number {
  if (closes.length < period) return closes[closes.length - 1] || 0;
  const slice = closes.slice(-period);
  return slice.reduce((a, b) => a + b, 0) / period;
}

/** Exponential Moving Average – full series */
function emaSeries(closes: number[], period: number): number[] {
  const k = 2 / (period + 1);
  const ema: number[] = [closes[0]];
  for (let i = 1; i < closes.length; i++) {
    ema.push(closes[i] * k + ema[i - 1] * (1 - k));
  }
  return ema;
}

/** Calculate MACD (DIF, DEA, BAR) for the full series; return last two */
function calcMACD(closes: number[]): {
  dif: number; dea: number; bar: number;
  prevDif: number; prevDea: number;
} {
  const ema12 = emaSeries(closes, 12);
  const ema26 = emaSeries(closes, 26);
  const difArr: number[] = ema12.map((v, i) => v - ema26[i]);
  // DEA = EMA(DIF, 9)
  const k = 2 / 10;
  const deaArr: number[] = [difArr[0]];
  for (let i = 1; i < difArr.length; i++) {
    deaArr.push(difArr[i] * k + deaArr[i - 1] * (1 - k));
  }
  const n = difArr.length - 1;
  return {
    dif: difArr[n], dea: deaArr[n],
    bar: (difArr[n] - deaArr[n]) * 2,
    prevDif: difArr[n - 1] ?? difArr[n],
    prevDea: deaArr[n - 1] ?? deaArr[n],
  };
}

/** Calculate RSI for given period; return latest value */
function calcRSI(closes: number[], period: number): number {
  if (closes.length < period + 1) return 50;
  let gainSum = 0, lossSum = 0;
  for (let i = closes.length - period; i < closes.length; i++) {
    const diff = closes[i] - closes[i - 1];
    if (diff > 0) gainSum += diff; else lossSum -= diff;
  }
  if (lossSum === 0) return 100;
  const rs = (gainSum / period) / (lossSum / period);
  return 100 - 100 / (1 + rs);
}

// ============================================================
// Fetch market data from Yahoo Finance
// ============================================================

// Helper: delay function
const delay = (ms: number) => new Promise(resolve => setTimeout(resolve, ms));

// Helper: retry with exponential backoff
async function retryWithBackoff<T>(
  fn: () => Promise<T>,
  maxRetries: number = 3,
  baseDelay: number = 5000
): Promise<T> {
  let lastError: Error | null = null;
  // Total attempts = 1 (initial) + maxRetries
  for (let attempt = 0; attempt <= maxRetries; attempt++) {
    try {
      if (attempt > 0) {
        // Exponential backoff: 5s, 10s, 20s
        const waitTime = baseDelay * Math.pow(2, attempt - 1);
        console.log(`[Retry ${attempt}/${maxRetries}] Waiting ${waitTime / 1000}s before retry...`);
        await delay(waitTime);
      }
      return await fn();
    } catch (err: any) {
      lastError = err;
      const errMsg = (err.message || '').toLowerCase();
      // Match various rate limit error patterns
      const isRateLimit = errMsg.includes('429') || 
                          errMsg.includes('too many requests') || 
                          errMsg.includes('rate limit') ||
                          errMsg.includes('edge:');
      if (isRateLimit && attempt < maxRetries) {
        console.log(`[Rate Limit] Attempt ${attempt + 1} failed: ${err.message}`);
        continue;
      }
      throw err;
    }
  }
  throw lastError || new Error('Max retries exceeded');
}

async function fetchMarketData(code: string): Promise<MarketData> {
  const symbol = toYahooSymbol(code);

  // Fetch 90-day historical data for indicator calculation
  const endDate = new Date();
  const startDate = new Date();
  startDate.setDate(startDate.getDate() - 120); // extra buffer

  // Sequential calls with retry to avoid rate limiting
  let history: any;
  let quote: any;
  
  try {
    history = await retryWithBackoff(() => yahooFinance.chart(symbol, {
      period1: startDate,
      period2: endDate,
      interval: '1d',
    }));
  } catch (err: any) {
    throw new Error(`获取 ${code} 历史数据失败: ${err.message}`);
  }
  
  // Wait before next request
  await delay(1000);
  
  try {
    quote = await retryWithBackoff(() => yahooFinance.quote(symbol));
  } catch (err: any) {
    // Quote is optional, continue without it
    quote = null;
  }

  const quotes = (history.quotes || []).filter((q: any) => q.close != null);
  if (quotes.length < 20) {
    throw new Error(`Insufficient data for ${code} (${symbol}): only ${quotes.length} bars`);
  }

  const bars: OHLCVBar[] = quotes.map((q: any) => ({
    date: new Date(q.date).toISOString().slice(0, 10),
    open: q.open ?? 0,
    high: q.high ?? 0,
    low: q.low ?? 0,
    close: q.close ?? 0,
    volume: q.volume ?? 0,
  }));

  const closes = bars.map(b => b.close);
  const volumes = bars.map(b => b.volume);
  const latest = bars[bars.length - 1];
  const prev = bars.length > 1 ? bars[bars.length - 2] : latest;

  // Moving averages
  const ma5 = sma(closes, 5);
  const ma10 = sma(closes, 10);
  const ma20 = sma(closes, 20);
  const ma60 = closes.length >= 60 ? sma(closes, 60) : ma20;

  // MACD
  const macd = calcMACD(closes);

  // RSI
  const rsi6 = calcRSI(closes, 6);
  const rsi12 = calcRSI(closes, 12);
  const rsi24 = calcRSI(closes, 24);

  // Volume ratio (today / 5d avg)
  const vol5dAvg = volumes.length >= 6
    ? volumes.slice(-6, -1).reduce((a, b) => a + b, 0) / 5
    : volumes[volumes.length - 1];
  const volumeRatio5d = vol5dAvg > 0 ? latest.volume / vol5dAvg : 1;

  // Resolve stock name
  let stockName = STOCK_NAME_MAP[code] || '';
  if (!stockName && quote) {
    stockName = quote.shortName || quote.longName || code;
  }
  if (!stockName) stockName = code;

  return {
    stockCode: code,
    stockName,
    bars,
    price: latest.close,
    changePercent: prev.close > 0 ? ((latest.close - prev.close) / prev.close) * 100 : 0,
    open: latest.open,
    high: latest.high,
    low: latest.low,
    prevClose: prev.close,
    volume: latest.volume,
    ma5, ma10, ma20, ma60,
    macdDif: macd.dif,
    macdDea: macd.dea,
    macdBar: macd.bar,
    prevMacdDif: macd.prevDif,
    prevMacdDea: macd.prevDea,
    rsi6, rsi12, rsi24,
    volumeRatio5d,
    pe: quote?.trailingPE ?? undefined,
    pb: quote?.priceToBook ?? undefined,
    marketCap: quote?.marketCap ?? undefined,
  };
}

// ============================================================
// Technical analysis (all local, precise data)
// ============================================================

const BIAS_THRESHOLD = 5.0;
const VOLUME_SHRINK_RATIO = 0.7;
const VOLUME_HEAVY_RATIO = 1.5;
const MA_SUPPORT_TOLERANCE = 0.02;

function performTechnicalAnalysis(d: MarketData): TechnicalAnalysis {
  const { price, ma5, ma10, ma20, bars } = d;

  // ---- 纯均线排列（只看MA大小关系，与价格无关）----
  let maArrangement: MAArrangement = 'MIXED';
  if (ma5 > ma10 && ma10 > ma20) {
    maArrangement = 'BULL';
  } else if (ma5 < ma10 && ma10 < ma20) {
    maArrangement = 'BEAR';
  }

  // ---- Trend（综合趋势状态，考虑价格位置）----
  let trendStatus: TrendStatus = 'CONSOLIDATION';
  let maAlignment = '均线缠绕，趋势不明';
  let trendStrength = 50;

  // 先判断价格与均线的关系
  const priceAboveAll = price > ma5 && price > ma10 && price > ma20;
  const priceBelowAll = price < ma5 && price < ma10 && price < ma20;

  if (ma5 > ma10 && ma10 > ma20) {
    // 标准多头排列
    const spread = ((ma5 - ma20) / ma20) * 100;
    let prevSpread = spread;
    if (bars.length >= 25) {
      const closes5ago = bars.slice(0, -5).map(b => b.close);
      const prevMa5 = sma(closes5ago, 5);
      const prevMa20 = sma(closes5ago, 20);
      if (prevMa20 > 0) prevSpread = ((prevMa5 - prevMa20) / prevMa20) * 100;
    }
    if (spread > prevSpread && spread > 5) {
      trendStatus = 'STRONG_BULL'; maAlignment = '强势多头排列，均线发散上行'; trendStrength = 90;
    } else {
      trendStatus = 'BULL'; maAlignment = '多头排列 MA5>MA10>MA20'; trendStrength = 75;
    }
  } else if (ma5 > ma10 && ma10 <= ma20) {
    trendStatus = 'WEAK_BULL'; maAlignment = '弱势多头，MA5>MA10 但 MA10≤MA20'; trendStrength = 55;
  } else if (ma5 < ma10 && ma10 < ma20) {
    // 均线空头排列，但要看价格位置
    if (priceAboveAll) {
      // 价格已突破所有均线，可能是反转信号
      trendStatus = 'WEAK_BULL'; maAlignment = '均线空头但价格突破，可能反转'; trendStrength = 55;
    } else {
      const spread = ((ma20 - ma5) / ma5) * 100;
      let prevSpread = spread;
      if (bars.length >= 25) {
        const closes5ago = bars.slice(0, -5).map(b => b.close);
        const prevMa5 = sma(closes5ago, 5);
        const prevMa20 = sma(closes5ago, 20);
        if (prevMa5 > 0) prevSpread = ((prevMa20 - prevMa5) / prevMa5) * 100;
      }
      if (spread > prevSpread && spread > 5) {
        trendStatus = 'STRONG_BEAR'; maAlignment = '强势空头排列，均线发散下行'; trendStrength = 10;
      } else {
        trendStatus = 'BEAR'; maAlignment = '空头排列 MA5<MA10<MA20'; trendStrength = 25;
      }
    }
  } else if (ma5 < ma10 && ma10 >= ma20) {
    trendStatus = 'WEAK_BEAR'; maAlignment = '弱势空头，MA5<MA10 但 MA10≥MA20'; trendStrength = 40;
  }

  // ---- Bias ----
  const biasMa5 = ma5 > 0 ? ((price - ma5) / ma5) * 100 : 0;
  const biasMa10 = ma10 > 0 ? ((price - ma10) / ma10) * 100 : 0;
  const biasMa20 = ma20 > 0 ? ((price - ma20) / ma20) * 100 : 0;

  // ---- Volume ----
  const latest = bars[bars.length - 1];
  const prev = bars.length > 1 ? bars[bars.length - 2] : latest;
  const priceChange = prev.close > 0 ? ((latest.close - prev.close) / prev.close) * 100 : 0;
  let volumeStatus: VolumeStatus = 'NORMAL';
  let volumeTrend = '量能正常';
  if (d.volumeRatio5d >= VOLUME_HEAVY_RATIO) {
    if (priceChange > 0) { volumeStatus = 'HEAVY_VOLUME_UP'; volumeTrend = '放量上涨，多头力量强劲'; }
    else { volumeStatus = 'HEAVY_VOLUME_DOWN'; volumeTrend = '放量下跌，注意风险'; }
  } else if (d.volumeRatio5d <= VOLUME_SHRINK_RATIO) {
    if (priceChange > 0) { volumeStatus = 'SHRINK_VOLUME_UP'; volumeTrend = '缩量上涨，上攻动能不足'; }
    else { volumeStatus = 'SHRINK_VOLUME_DOWN'; volumeTrend = '缩量回调，洗盘特征明显（好）'; }
  }

  // ---- Support ----
  const supportMa5 = ma5 > 0 && price >= ma5 && Math.abs(price - ma5) / ma5 <= MA_SUPPORT_TOLERANCE;
  const supportMa10 = ma10 > 0 && price >= ma10 && Math.abs(price - ma10) / ma10 <= MA_SUPPORT_TOLERANCE;

  // ---- MACD ----
  let macdStatus: MACDStatus = 'BULLISH';
  let macdSignal = 'MACD 中性区域';
  const prevDifDea = d.prevMacdDif - d.prevMacdDea;
  const currDifDea = d.macdDif - d.macdDea;
  const isGoldenCross = prevDifDea <= 0 && currDifDea > 0;
  const isDeathCross = prevDifDea >= 0 && currDifDea < 0;
  const isCrossingUp = d.prevMacdDif <= 0 && d.macdDif > 0;
  const isCrossingDown = d.prevMacdDif >= 0 && d.macdDif < 0;

  if (isGoldenCross && d.macdDif > 0) {
    macdStatus = 'GOLDEN_CROSS_ZERO'; macdSignal = '零轴上金叉，强烈买入信号';
  } else if (isCrossingUp) {
    macdStatus = 'CROSSING_UP'; macdSignal = 'DIF上穿零轴，趋势转强';
  } else if (isGoldenCross) {
    macdStatus = 'GOLDEN_CROSS'; macdSignal = '金叉，趋势向上';
  } else if (isDeathCross) {
    macdStatus = 'DEATH_CROSS'; macdSignal = '死叉，趋势向下';
  } else if (isCrossingDown) {
    macdStatus = 'CROSSING_DOWN'; macdSignal = 'DIF下穿零轴，趋势转弱';
  } else if (d.macdDif > 0 && d.macdDea > 0) {
    macdStatus = 'BULLISH'; macdSignal = '多头排列，持续上涨';
  } else if (d.macdDif < 0 && d.macdDea < 0) {
    macdStatus = 'BEARISH'; macdSignal = '空头排列，持续下跌';
  }

  // ---- RSI ----
  let rsiStatus: RSIStatus = 'NEUTRAL';
  let rsiSignal = `RSI中性(${d.rsi12.toFixed(1)})，震荡整理中`;
  if (d.rsi12 > 70) { rsiStatus = 'OVERBOUGHT'; rsiSignal = `RSI超买(${d.rsi12.toFixed(1)}>70)，短期回调风险高`; }
  else if (d.rsi12 > 60) { rsiStatus = 'STRONG_BUY'; rsiSignal = `RSI强势(${d.rsi12.toFixed(1)})，多头力量充足`; }
  else if (d.rsi12 >= 40) { rsiStatus = 'NEUTRAL'; rsiSignal = `RSI中性(${d.rsi12.toFixed(1)})，震荡整理中`; }
  else if (d.rsi12 >= 30) { rsiStatus = 'WEAK'; rsiSignal = `RSI弱势(${d.rsi12.toFixed(1)})，关注反弹`; }
  else { rsiStatus = 'OVERSOLD'; rsiSignal = `RSI超卖(${d.rsi12.toFixed(1)}<30)，反弹机会大`; }

  // ---- Scoring (100-point system) ----
  const trendScores: Record<TrendStatus, number> = {
    STRONG_BULL: 30, BULL: 26, WEAK_BULL: 18, CONSOLIDATION: 12,
    WEAK_BEAR: 8, BEAR: 4, STRONG_BEAR: 0,
  };
  const volumeScores: Record<VolumeStatus, number> = {
    SHRINK_VOLUME_DOWN: 15, HEAVY_VOLUME_UP: 12, NORMAL: 10,
    SHRINK_VOLUME_UP: 6, HEAVY_VOLUME_DOWN: 0,
  };
  const macdScores: Record<MACDStatus, number> = {
    GOLDEN_CROSS_ZERO: 15, GOLDEN_CROSS: 12, CROSSING_UP: 10,
    BULLISH: 8, BEARISH: 2, CROSSING_DOWN: 0, DEATH_CROSS: 0,
  };
  const rsiScores: Record<RSIStatus, number> = {
    OVERSOLD: 10, STRONG_BUY: 8, NEUTRAL: 5, WEAK: 3, OVERBOUGHT: 0,
  };

  let score = 0;
  const reasons: string[] = [];
  const risks: string[] = [];

  // Trend (30)
  score += trendScores[trendStatus];
  if (trendStatus === 'STRONG_BULL' || trendStatus === 'BULL')
    reasons.push(`${trendStatus === 'STRONG_BULL' ? '强势多头' : '多头排列'}，顺势做多`);
  else if (trendStatus === 'BEAR' || trendStatus === 'STRONG_BEAR')
    risks.push(`${trendStatus === 'STRONG_BEAR' ? '强势空头' : '空头排列'}，不宜做多`);

  // Bias (20)
  const isStrongTrend = trendStatus === 'STRONG_BULL' && trendStrength >= 70;
  const effectiveThreshold = isStrongTrend ? BIAS_THRESHOLD * 1.5 : BIAS_THRESHOLD;
  if (biasMa5 < 0) {
    if (biasMa5 > -3) { score += 20; reasons.push(`价格略低于MA5(${biasMa5.toFixed(1)}%)，回踩买点`); }
    else if (biasMa5 > -5) { score += 16; reasons.push(`价格回踩MA5(${biasMa5.toFixed(1)}%)，观察支撑`); }
    else { score += 8; risks.push(`乖离率过大(${biasMa5.toFixed(1)}%)，可能破位`); }
  } else if (biasMa5 < 2) {
    score += 18; reasons.push(`价格贴近MA5(${biasMa5.toFixed(1)}%)，介入好时机`);
  } else if (biasMa5 < BIAS_THRESHOLD) {
    score += 14; reasons.push(`价格略高于MA5(${biasMa5.toFixed(1)}%)，可小仓介入`);
  } else if (biasMa5 > effectiveThreshold) {
    score += 4; risks.push(`乖离率过高(${biasMa5.toFixed(1)}%>${effectiveThreshold.toFixed(1)}%)，严禁追高`);
  } else if (isStrongTrend) {
    score += 10; reasons.push(`强势趋势中乖离率偏高(${biasMa5.toFixed(1)}%)，可轻仓追踪`);
  } else {
    score += 4; risks.push(`乖离率过高(${biasMa5.toFixed(1)}%>${BIAS_THRESHOLD.toFixed(1)}%)，严禁追高`);
  }

  // Volume (15)
  score += volumeScores[volumeStatus];
  if (volumeStatus === 'SHRINK_VOLUME_DOWN') reasons.push('缩量回调，主力洗盘');
  else if (volumeStatus === 'HEAVY_VOLUME_DOWN') risks.push('放量下跌，注意风险');

  // Support (10)
  if (supportMa5) { score += 5; reasons.push('MA5支撑有效'); }
  if (supportMa10) { score += 5; reasons.push('MA10支撑有效'); }

  // MACD (15)
  score += macdScores[macdStatus];
  if (macdStatus === 'GOLDEN_CROSS_ZERO' || macdStatus === 'GOLDEN_CROSS') reasons.push(macdSignal);
  else if (macdStatus === 'DEATH_CROSS' || macdStatus === 'CROSSING_DOWN') risks.push(macdSignal);

  // RSI (10)
  score += rsiScores[rsiStatus];
  if (rsiStatus === 'OVERSOLD' || rsiStatus === 'STRONG_BUY') reasons.push(rsiSignal);
  else if (rsiStatus === 'OVERBOUGHT') risks.push(rsiSignal);

  // Buy signal
  let buySignal: BuySignal = 'WAIT';
  if (score >= 75 && (trendStatus === 'STRONG_BULL' || trendStatus === 'BULL')) buySignal = 'STRONG_BUY';
  else if (score >= 60 && ['STRONG_BULL', 'BULL', 'WEAK_BULL'].includes(trendStatus)) buySignal = 'BUY';
  else if (score >= 45) buySignal = 'HOLD';
  else if (score >= 30) buySignal = 'WAIT';
  else if (trendStatus === 'BEAR' || trendStatus === 'STRONG_BEAR') buySignal = 'STRONG_SELL';
  else buySignal = 'SELL';

  return {
    maArrangement,
    trendStatus, maAlignment, trendStrength,
    biasMa5, biasMa10, biasMa20,
    volumeStatus, volumeTrend, volumeRatio5d: d.volumeRatio5d,
    supportMa5, supportMa10,
    macdStatus, macdSignal, rsiStatus, rsiSignal,
    buySignal, signalScore: score,
    signalReasons: reasons, riskFactors: risks,
  };
}

// ============================================================
// News search via Tavily
// ============================================================

async function fetchNews(
  client: TavilyClient,
  code: string,
  stockName: string,
  market: 'A' | 'US' | 'HK'
): Promise<NewsItem[]> {
  let query: string;
  if (market === 'US') {
    query = `${code} stock latest news earnings`;
  } else if (market === 'HK') {
    query = `${stockName} ${code} 港股 最新消息 新闻`;
  } else {
    query = `${stockName} ${code} A股 最新消息 新闻`;
  }

  try {
    const result = await client.search(query, {
      searchDepth: 'basic',
      topic: 'news',
      maxResults: 10,
      timeRange: 'week',
      excludeDomains: ['tipranks.com'],
    });
    
    return (result.results || [])
      .map((r: any) => ({
        title: r.title || 'Untitled',
        snippet: (r.content || r.snippet || '').slice(0, 500),
        url: r.url || '',
        source: r.url ? new URL(r.url).hostname : 'unknown',
        publishedDate: r.publishedDate || undefined,
      }))
      .slice(0, 10);
  } catch (e) {
    console.warn('News search failed:', e);
    return [];
  }
}

// ============================================================
// Build LLM prompt for Hunyuan Chat
// ============================================================

const ANALYSIS_SYSTEM_PROMPT = `你是一位专业的股票分析师。根据提供的行情数据、技术指标和新闻，输出结构清晰的 Markdown 分析报告。

## 核心交易规则

1. **不追高**：乖离率 > 5% 时严禁买入
2. **趋势交易**：只做多头排列（MA5 > MA10 > MA20）的股票
3. **买点偏好**：缩量回踩 MA5/MA10 支撑为最佳买点
4. **风险排查**：关注减持、业绩预亏、监管处罚等利空
5. **强势放宽**：强势多头可放宽乖离率至 7.5%

## 输出格式要求

**严格按以下格式输出，不要添加额外的说明文字，不要用代码块包裹：**

# {股票名称}({股票代码}) 决策仪表盘

> 📅 {日期} | 评分 **{分数}/100** | {看多/震荡/看空} | **{买入/持有/观望/卖出}**

---

## 📊 核心结论

**{一句话核心判断，如"多头趋势良好，回踩MA5可介入"}**

| 状态 | 建议 |
|:--|:--|
| 空仓 | {具体建议} |
| 持仓 | {具体建议} |

---

## 📈 趋势分析

### 均线系统

**排列状态**: {直接使用数据中的"均线排列"判断} {✅/⚠️/❌}

| 均线 | 数值 | 方向 |
|:--|:--|:--|
| MA5 | {值} | {↑上升/→走平/↓下降} |
| MA10 | {值} | {↑上升/→走平/↓下降} |
| MA20 | {值} | {↑上升/→走平/↓下降} |

{1-2句话，直接引用数据中的"均线排列"和"趋势状态"结论，不要输出判断规则}

### 价格位置

| 指标 | 数值 | 判断 |
|:--|:--|:--|
| 现价 | {价格} | {站上MA5/回踩MA5/跌破MA5测试MA10/跌破所有均线} |
| 乖离率(MA5) | {值}% | {安全(<3%)/警戒(3-5%)/危险(>5%)} |
| 支撑位 | {价格} | {有效/待验证} |
| 压力位 | {价格} | {距离X%} |

{1-2句话分析当前位置是否适合介入，不要输出判断规则}

### 量能特征

量比 **{值}**，{放量/缩量/正常}。{一句话量价分析}

---

## 🔧 技术指标

### MACD

| DIF | DEA | 柱 | 信号 |
|:--|:--|:--|:--|
| {值} | {值} | {值} | {金叉/死叉/多头/空头} |

{1-2句话解读MACD当前状态和趋势含义}

### RSI

| RSI6 | RSI12 | RSI24 | 状态 |
|:--|:--|:--|:--|
| {值} | {值} | {值} | {超买(任一RSI>70)/超卖(任一RSI<30)/中性} |

{1-2句话解读RSI当前状态，注意：任一RSI>70即为超买，任一RSI<30即为超卖}

---

## 📰 舆情分析

**情绪**: {积极/中性/消极}

**重要**：只展示与该股票直接相关的新闻（标题或内容明确提到该公司），过滤掉泛市场新闻。如果没有直接相关的新闻，写"暂无直接相关新闻"。

相关新闻:

1. [{新闻标题}]({新闻链接}) - {利好/利空/中性}
   {引用新闻中的具体内容，说明判断依据}

2. ...（只展示直接相关的，最多5条）

---

## 📋 评分明细

**{分数}/100** → **{信号}**

| 检查项 | 状态 |
|:--|:--|
| 均线排列 | {根据"均线排列(纯MA关系)"字段：BULL→✅多头排列 / BEAR→❌空头排列 / MIXED→⚠️均线缠绕} |
| 乖离率 | {✅ 安全(<5%) / ❌ 过高(>5%)} |
| 量能配合 | {✅ 良好 / ⚠️ 一般 / ❌ 异常} |
| 舆情风险 | {✅ 无重大利空 / ❌ 存在利空} |

**优势**: {买入理由，用顿号分隔}

**风险**: {风险因素，用顿号分隔}

---

> ⚠️ 本报告由AI生成，仅供参考，不构成投资建议。股市有风险，投资需谨慎。`;

function buildAnalysisPrompt(
  data: MarketData,
  analysis: TechnicalAnalysis,
  news: NewsItem[],
): string {
  const now = new Date();
  const dateStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;

  // Recent 10 bars for K-line pattern
  const recentBars = data.bars.slice(-10).map(b =>
    `${b.date}: O=${b.open.toFixed(2)} H=${b.high.toFixed(2)} L=${b.low.toFixed(2)} C=${b.close.toFixed(2)} V=${b.volume}`
  ).join('\n');

  // Recent 20-day high/low
  const recent20 = data.bars.slice(-20);
  const recent20High = recent20.reduce((max, b) => Math.max(max, b.high), 0);
  const recent20Low = recent20.reduce((min, b) => Math.min(min, b.low), Infinity);

  // News section with full content for LLM analysis
  const newsText = news.length > 0
    ? news.map((n, i) => `【新闻${i + 1}】
标题: ${n.title}
来源: ${n.source}${n.publishedDate ? ` | 日期: ${n.publishedDate}` : ''}
内容: ${n.snippet}
链接: ${n.url}
`).join('\n')
    : '暂无最新消息';

  return `请分析以下股票并生成决策仪表盘报告。

## 基本信息
- 股票代码: ${data.stockCode}
- 股票名称: ${data.stockName}
- 分析日期: ${dateStr}
- 市场: ${detectMarket(data.stockCode) === 'A' ? 'A股' : detectMarket(data.stockCode) === 'HK' ? '港股' : '美股'}

## 行情数据
- 最新价: ${data.price.toFixed(2)}
- 涨跌幅: ${data.changePercent.toFixed(2)}%
- 开盘: ${data.open.toFixed(2)} | 最高: ${data.high.toFixed(2)} | 最低: ${data.low.toFixed(2)}
- 昨收: ${data.prevClose.toFixed(2)}
- 成交量: ${data.volume}

## 均线数据
- MA5: ${data.ma5.toFixed(2)}
- MA10: ${data.ma10.toFixed(2)}
- MA20: ${data.ma20.toFixed(2)}
- MA60: ${data.ma60.toFixed(2)}

## 乖离率
- 乖离率(MA5): ${analysis.biasMa5.toFixed(2)}%
- 乖离率(MA10): ${analysis.biasMa10.toFixed(2)}%
- 乖离率(MA20): ${analysis.biasMa20.toFixed(2)}%

## MACD 指标
- DIF: ${data.macdDif.toFixed(4)}
- DEA: ${data.macdDea.toFixed(4)}
- MACD柱: ${data.macdBar.toFixed(4)}
- 前日DIF: ${data.prevMacdDif.toFixed(4)} | 前日DEA: ${data.prevMacdDea.toFixed(4)}
- MACD状态: ${analysis.macdStatus} - ${analysis.macdSignal}

## RSI 指标
- RSI(6): ${data.rsi6.toFixed(1)}
- RSI(12): ${data.rsi12.toFixed(1)}
- RSI(24): ${data.rsi24.toFixed(1)}
- RSI状态: ${analysis.rsiStatus} - ${analysis.rsiSignal}

## 量能数据
- 量比(vs 5日均量): ${analysis.volumeRatio5d.toFixed(2)}
- 量能状态: ${analysis.volumeStatus} - ${analysis.volumeTrend}

## 趋势判断
- 均线排列(纯MA关系): ${analysis.maArrangement}  // BULL=多头排列, BEAR=空头排列, MIXED=均线缠绕
- 均线描述: ${analysis.maAlignment}
- 趋势状态: ${analysis.trendStatus}
- 趋势强度: ${analysis.trendStrength}/100
- MA5支撑: ${analysis.supportMa5 ? '有效' : '无'}
- MA10支撑: ${analysis.supportMa10 ? '有效' : '无'}

## 估值数据
- PE(TTM): ${data.pe !== undefined ? data.pe.toFixed(1) : '暂无'}
- PB: ${data.pb !== undefined ? data.pb.toFixed(2) : '暂无'}
- 总市值: ${data.marketCap !== undefined ? (data.marketCap / 1e8).toFixed(0) + '亿' : '暂无'}

## 评分系统 (100分制)
- 综合评分: ${analysis.signalScore}/100
- 交易信号: ${analysis.buySignal}
- 买入理由: ${analysis.signalReasons.length > 0 ? analysis.signalReasons.join('；') : '无'}
- 风险因素: ${analysis.riskFactors.length > 0 ? analysis.riskFactors.join('；') : '无'}

## 近20日价格区间
- 20日最高: ${recent20High.toFixed(2)}
- 20日最低: ${recent20Low.toFixed(2)}

## 近10日K线数据
${recentBars}

## 最新新闻 (共${news.length}条)
${newsText}

请严格按照系统提示中的 Markdown 模板格式输出决策仪表盘报告。直接输出 Markdown 内容，不要包裹在代码块中。`;
}

// ============================================================
// Main StockAnalyzer class
// ============================================================

export class StockAnalyzer {
  private tavilyClient: TavilyClient;
  private hunyuanClient: HunyuanClient;

  constructor(config?: StockAnalysisConfig) {
    // Tavily client
    const isSandbox = process.env.X_IDE_AUTH_PROXY !== undefined;
    const apiKey = config?.tavilyApiKey || process.env.TAVILY_WEB_SEARCH_API_KEY || (isSandbox ? 'mock_tavily_api_key' : '');

    if (!apiKey) {
      throw new Error('TAVILY_WEB_SEARCH_API_KEY is required for news search. Set it via environment variable or pass tavilyApiKey in config.');
    }

    const useSandbox = isSandbox && apiKey === 'mock_tavily_api_key';
    this.tavilyClient = useSandbox
      ? tavily({ apiKey, apiBaseURL: 'http://tavily.auth-proxy.local' })
      : tavily({ apiKey });

    // Hunyuan client (zero-config in Genie sandbox)
    this.hunyuanClient = createHunyuanClient();
  }

  /**
   * Analyze a stock by code.
   *
   * 1. Fetches OHLCV history from Yahoo Finance
   * 2. Calculates MA/MACD/RSI/scoring locally (precise data)
   * 3. Searches news via Tavily
   * 4. Sends structured data to Hunyuan LLM for intelligent analysis report generation
   *
   * @param stockCode - Stock code (A-share: 600519, US: AAPL, HK: 00700)
   */
  async analyze(stockCode: string): Promise<AnalysisResult> {
    const market = detectMarket(stockCode);

    // Step 1 & 2: Fetch market data and news in parallel
    const [marketData, news] = await Promise.all([
      fetchMarketData(stockCode),
      fetchNews(this.tavilyClient, stockCode, STOCK_NAME_MAP[stockCode] || stockCode, market),
    ]);

    // Step 3: Perform technical analysis (all local, precise)
    const technicalAnalysis = performTechnicalAnalysis(marketData);

    // Step 4: Build prompt and call Hunyuan LLM for analysis report
    const prompt = buildAnalysisPrompt(marketData, technicalAnalysis, news);
    const llmResponse = await this.hunyuanClient.chatCompletions(
      [
        { Role: 'system', Content: ANALYSIS_SYSTEM_PROMPT },
        { Role: 'user', Content: prompt },
      ],
      {
        Model: 'hunyuan-2.0-instruct-20251111',
        Temperature: 0.3,  // Lower temperature for more precise analysis
        TopP: 0.85,
      }
    );

    const markdown = llmResponse.Choices[0]?.Message?.Content || '';

    return { markdown, marketData, news, technicalAnalysis };
  }
}

/**
 * Create StockAnalyzer instance
 *
 * @example
 * const analyzer = createStockAnalyzer();
 * const result = await analyzer.analyze('600519');
 * console.log(result.markdown);
 */
export function createStockAnalyzer(config?: StockAnalysisConfig): StockAnalyzer {
  return new StockAnalyzer(config);
}
