<!-- Template: 科技风-矩阵终端 (TOC #3575) -->
<div class="w-[1440px] h-[810px] shadow-2xl relative overflow-hidden" style="background: #0a0f0a;">
    <div class="w-[1350px] h-[720px] absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 relative">
        <!-- 矩阵代码雨背景 -->
        <div style="position: absolute; inset: 0; font-family: 'Courier New', monospace; font-size: 11px; color: rgba(0, 255, 65, 0.08); line-height: 1.6; overflow: hidden; pointer-events: none;">
            <div style="position: absolute; left: 3%; top: 0;">01001101<br/>11010010<br/>00101100<br/>10110011<br/>01001010<br/>11100001<br/>00110101<br/>10001110<br/>01010011<br/>11001001<br/>00100110<br/>10111010<br/>01101001<br/>11000110<br/>00010111</div>
            <div style="position: absolute; left: 13%; top: 30px;">10110100<br/>01001011<br/>11010001<br/>00101110<br/>10010011<br/>01110100<br/>11001010<br/>00011101<br/>10100110<br/>01010011<br/>11100001<br/>00110110</div>
            <div style="position: absolute; right: 12%; top: 10px;">00101101<br/>10010110<br/>01101001<br/>11010011<br/>00110100<br/>10001011<br/>01110010<br/>11001101<br/>00100011<br/>10110100<br/>01001110<br/>11010001<br/>00101010</div>
            <div style="position: absolute; right: 3%; top: 50px;">11001010<br/>00100111<br/>10011001<br/>01100110<br/>11010010<br/>00101100<br/>10110011<br/>01001010<br/>11100001<br/>00110101</div>
        </div>

        <!-- SVG 电路板纹理 -->
        <svg style="position: absolute; width: 100%; height: 100%; opacity: 0.06;" viewBox="0 0 1350 720">
            <path d="M0 200 H200 L220 180 H400" stroke="#00ff41" stroke-width="1.5" fill="none"/>
            <path d="M0 400 H150 L170 420 H350 L370 400 H500" stroke="#00ff41" stroke-width="1.5" fill="none"/>
            <path d="M0 550 H100 L120 530 H280" stroke="#00ff41" stroke-width="1.5" fill="none"/>
            <path d="M1350 150 H1150 L1130 170 H950" stroke="#00ff41" stroke-width="1.5" fill="none"/>
            <path d="M1350 350 H1200 L1180 330 H1000" stroke="#00ff41" stroke-width="1.5" fill="none"/>
            <path d="M1350 600 H1100 L1080 580 H900" stroke="#00ff41" stroke-width="1.5" fill="none"/>
            <circle cx="200" cy="200" r="3" fill="#00ff41"/>
            <circle cx="400" cy="180" r="3" fill="#00ff41"/>
            <circle cx="350" cy="420" r="3" fill="#00ff41"/>
            <circle cx="950" cy="170" r="3" fill="#00ff41"/>
            <circle cx="1000" cy="330" r="3" fill="#00ff41"/>
        </svg>

        <div class="flex h-full items-center">
            <!-- 左侧终端标题 -->
            <div style="width: 320px; padding: 40px; font-family: 'Courier New', monospace;">
                <div style="color: rgba(0, 255, 65, 0.4); font-size: 12px; margin-bottom: 8px;">root@system:~$</div>
                <h1 style="font-size: 42px; font-weight: 900; color: #00ff41; letter-spacing: 3px; line-height: 1.2; margin-bottom: 16px;">
                    SYSTEM<br/>INDEX
                </h1>
                <div style="display: flex; gap: 4px; margin-bottom: 24px;">
                    <div style="width: 40px; height: 3px; background: #00ff41;"></div>
                    <div style="width: 8px; height: 3px; background: #00ff41;"></div>
                    <div style="width: 4px; height: 3px; background: #00ff41;"></div>
                </div>
                <div style="color: rgba(0, 255, 65, 0.3); font-size: 11px; line-height: 1.8;">
                    STATUS: ONLINE<br/>
                    MODULES: LOADED<br/>
                    ACCESS: GRANTED
                </div>
            </div>

            <!-- 右侧条目列表 - 终端行号风格 -->
            <div style="flex: 1; padding: 30px 40px; font-family: 'Courier New', monospace;">
                <div style="display: flex; flex-direction: column; gap: 12px;">

                    <div style="display: flex; align-items: center; gap: 16px; padding: 14px 20px; background: rgba(0, 255, 65, 0.04);">
                        <div style="color: rgba(0, 255, 65, 0.3); font-size: 12px; width: 30px; text-align: right;">001</div>
                        <div style="width: 2px; height: 36px; background: #00ff41;"></div>
                        <div style="width: 36px; height: 36px; background: rgba(0, 255, 65, 0.15); display: flex; align-items: center; justify-content: center;">
                            <span style="color: #00ff41; font-size: 18px; font-weight: 700;">1</span>
                        </div>
                        <div style="flex: 1;">
                            <h3 style="color: #00ff41; font-size: 18px; font-weight: 700; margin-bottom: 2px;">项目背景</h3>
                            <p style="color: rgba(0, 255, 65, 0.5); font-size: 12px;">了解项目的起源与目标</p>
                        </div>
                        <div style="color: rgba(0, 255, 65, 0.2); font-size: 11px;">[LOADED]</div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 16px; padding: 14px 20px; background: rgba(0, 255, 65, 0.04);">
                        <div style="color: rgba(0, 255, 65, 0.3); font-size: 12px; width: 30px; text-align: right;">002</div>
                        <div style="width: 2px; height: 36px; background: #00ff41;"></div>
                        <div style="width: 36px; height: 36px; background: rgba(0, 255, 65, 0.15); display: flex; align-items: center; justify-content: center;">
                            <span style="color: #00ff41; font-size: 18px; font-weight: 700;">2</span>
                        </div>
                        <div style="flex: 1;">
                            <h3 style="color: #00ff41; font-size: 18px; font-weight: 700; margin-bottom: 2px;">核心功能</h3>
                            <p style="color: rgba(0, 255, 65, 0.5); font-size: 12px;">产品的主要功能介绍</p>
                        </div>
                        <div style="color: rgba(0, 255, 65, 0.2); font-size: 11px;">[LOADED]</div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 16px; padding: 14px 20px; background: rgba(0, 255, 65, 0.04);">
                        <div style="color: rgba(0, 255, 65, 0.3); font-size: 12px; width: 30px; text-align: right;">003</div>
                        <div style="width: 2px; height: 36px; background: #00ff41;"></div>
                        <div style="width: 36px; height: 36px; background: rgba(0, 255, 65, 0.15); display: flex; align-items: center; justify-content: center;">
                            <span style="color: #00ff41; font-size: 18px; font-weight: 700;">3</span>
                        </div>
                        <div style="flex: 1;">
                            <h3 style="color: #00ff41; font-size: 18px; font-weight: 700; margin-bottom: 2px;">技术架构</h3>
                            <p style="color: rgba(0, 255, 65, 0.5); font-size: 12px;">系统设计与技术选型</p>
                        </div>
                        <div style="color: rgba(0, 255, 65, 0.2); font-size: 11px;">[LOADED]</div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 16px; padding: 14px 20px; background: rgba(0, 255, 65, 0.04);">
                        <div style="color: rgba(0, 255, 65, 0.3); font-size: 12px; width: 30px; text-align: right;">004</div>
                        <div style="width: 2px; height: 36px; background: #00ff41;"></div>
                        <div style="width: 36px; height: 36px; background: rgba(0, 255, 65, 0.15); display: flex; align-items: center; justify-content: center;">
                            <span style="color: #00ff41; font-size: 18px; font-weight: 700;">4</span>
                        </div>
                        <div style="flex: 1;">
                            <h3 style="color: #00ff41; font-size: 18px; font-weight: 700; margin-bottom: 2px;">实施计划</h3>
                            <p style="color: rgba(0, 255, 65, 0.5); font-size: 12px;">项目的推进与时间安排</p>
                        </div>
                        <div style="color: rgba(0, 255, 65, 0.2); font-size: 11px;">[LOADED]</div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 16px; padding: 14px 20px; background: rgba(0, 255, 65, 0.04);">
                        <div style="color: rgba(0, 255, 65, 0.3); font-size: 12px; width: 30px; text-align: right;">005</div>
                        <div style="width: 2px; height: 36px; background: #00ff41;"></div>
                        <div style="width: 36px; height: 36px; background: rgba(0, 255, 65, 0.15); display: flex; align-items: center; justify-content: center;">
                            <span style="color: #00ff41; font-size: 18px; font-weight: 700;">5</span>
                        </div>
                        <div style="flex: 1;">
                            <h3 style="color: #00ff41; font-size: 18px; font-weight: 700; margin-bottom: 2px;">数据分析</h3>
                            <p style="color: rgba(0, 255, 65, 0.5); font-size: 12px;">关键数据与洞察分析</p>
                        </div>
                        <div style="color: rgba(0, 255, 65, 0.2); font-size: 11px;">[LOADED]</div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 16px; padding: 14px 20px; background: rgba(0, 255, 65, 0.04);">
                        <div style="color: rgba(0, 255, 65, 0.3); font-size: 12px; width: 30px; text-align: right;">006</div>
                        <div style="width: 2px; height: 36px; background: #00ff41;"></div>
                        <div style="width: 36px; height: 36px; background: rgba(0, 255, 65, 0.15); display: flex; align-items: center; justify-content: center;">
                            <span style="color: #00ff41; font-size: 18px; font-weight: 700;">6</span>
                        </div>
                        <div style="flex: 1;">
                            <h3 style="color: #00ff41; font-size: 18px; font-weight: 700; margin-bottom: 2px;">总结展望</h3>
                            <p style="color: rgba(0, 255, 65, 0.5); font-size: 12px;">未来规划与发展方向</p>
                        </div>
                        <div style="color: rgba(0, 255, 65, 0.2); font-size: 11px;">[LOADED]</div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>