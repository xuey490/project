/**
 * Excel/CSV Parser Wrapper
 *
 * Unified tabular file parser supporting xlsx, xls, csv, tsv formats.
 * Uses SheetJS for Excel files and csv-parse for CSV files.
 *
 * @example
 * const parser = createParser();
 * const result = await parser.parse(buffer, { filename: 'data.xlsx', headers: true });
 * console.log(result.headers); // ['Column1', 'Column2', 'Column3']
 * console.log(result.rows);    // [{Column1: 'a', Column2: 'b'}, ...]
 */

import * as XLSX from 'xlsx';
import { parse as csvParse } from 'csv-parse/sync';

/**
 * Parse options
 */
export interface ParseOptions {
  /** Filename (used to determine format) */
  filename: string;
  /** Worksheet index or name (Excel only) */
  sheet?: number | string;
  /** Whether first row is headers */
  headers?: boolean;
  /** Skip empty rows */
  skipEmptyRows?: boolean;
  /** CSV delimiter (auto-detected) */
  delimiter?: string;
  /** Parse dates as Date objects */
  parseDates?: boolean;
  /** Maximum row limit (0 = unlimited) */
  maxRows?: number;
}

/**
 * Parse result
 */
export interface ParsedData {
  /** Array of header column names */
  headers: string[];
  /** Array of data rows (object format) */
  rows: Record<string, unknown>[];
  /** Raw data (array format) */
  rawData: unknown[][];
  /** Worksheet name (Excel only) */
  sheetName?: string;
  /** Total row count */
  totalRows: number;
}

/**
 * Supported file extensions
 */
const EXCEL_EXTENSIONS = ['.xlsx', '.xls', '.xlsm', '.xlsb'];
const CSV_EXTENSIONS = ['.csv', '.tsv', '.txt'];

/**
 * Excel/CSV Parser class
 */
export class ExcelParser {
  /**
   * Parse file Buffer
   *
   * @param buffer - File content
   * @param options - Parse options
   * @returns Parse result
   *
   * @example
   * const result = await parser.parse(buffer, {
   *   filename: 'sales.xlsx',
   *   headers: true,
   *   sheet: 'Sheet1'
   * });
   */
  async parse(buffer: Buffer, options: ParseOptions): Promise<ParsedData> {
    const ext = this.getExtension(options.filename);

    if (EXCEL_EXTENSIONS.includes(ext)) {
      return this.parseExcel(buffer, options);
    } else if (CSV_EXTENSIONS.includes(ext)) {
      return this.parseCsv(buffer, options);
    } else {
      throw new Error(`Unsupported file format: ${ext}`);
    }
  }

  /**
   * Get all worksheet names from Excel file
   *
   * @param buffer - File content
   * @param filename - Filename
   * @returns Array of worksheet names
   */
  async getSheetNames(buffer: Buffer, filename: string): Promise<string[]> {
    const ext = this.getExtension(filename);

    if (!EXCEL_EXTENSIONS.includes(ext)) {
      throw new Error('Only Excel files support multiple worksheets');
    }

    const workbook = XLSX.read(buffer, { type: 'buffer' });
    return workbook.SheetNames;
  }

  /**
   * Parse Excel file
   */
  private parseExcel(buffer: Buffer, options: ParseOptions): ParsedData {
    const {
      sheet = 0,
      headers = true,
      skipEmptyRows = true,
      parseDates = false,
      maxRows = 0
    } = options;

    // Read workbook
    const workbook = XLSX.read(buffer, {
      type: 'buffer',
      cellDates: parseDates,
      sheetRows: maxRows > 0 ? maxRows + (headers ? 1 : 0) : 0
    });

    // Get worksheet
    let sheetName: string;
    if (typeof sheet === 'number') {
      sheetName = workbook.SheetNames[sheet];
      if (!sheetName) {
        throw new Error(`Worksheet index ${sheet} does not exist`);
      }
    } else {
      if (!workbook.SheetNames.includes(sheet)) {
        throw new Error(`Worksheet "${sheet}" does not exist`);
      }
      sheetName = sheet;
    }

    const worksheet = workbook.Sheets[sheetName];

    // Convert to array
    const rawData: unknown[][] = XLSX.utils.sheet_to_json(worksheet, {
      header: 1,
      defval: '',
      blankrows: !skipEmptyRows
    });

    // Process headers and data
    return this.processRawData(rawData, headers, sheetName);
  }

  /**
   * Parse CSV file
   */
  private parseCsv(buffer: Buffer, options: ParseOptions): ParsedData {
    const {
      headers = true,
      skipEmptyRows = true,
      delimiter,
      maxRows = 0
    } = options;

    const content = buffer.toString('utf-8');

    // Auto-detect delimiter
    const detectedDelimiter = delimiter || this.detectDelimiter(content, options.filename);

    // Parse CSV
    const rawData: unknown[][] = csvParse(content, {
      delimiter: detectedDelimiter,
      skip_empty_lines: skipEmptyRows,
      relax_column_count: true,
      to: maxRows > 0 ? maxRows + (headers ? 1 : 0) : undefined
    });

    return this.processRawData(rawData, headers);
  }

  /**
   * Process raw data, generate unified format
   */
  private processRawData(
    rawData: unknown[][],
    hasHeaders: boolean,
    sheetName?: string
  ): ParsedData {
    if (rawData.length === 0) {
      return {
        headers: [],
        rows: [],
        rawData: [],
        sheetName,
        totalRows: 0
      };
    }

    let headers: string[];
    let dataRows: unknown[][];

    if (hasHeaders) {
      // First row as headers
      headers = (rawData[0] as unknown[]).map((val, idx) =>
        val !== undefined && val !== null && val !== ''
          ? String(val)
          : `Column${idx + 1}`
      );
      dataRows = rawData.slice(1);
    } else {
      // Auto-generate column names
      const colCount = Math.max(...rawData.map(row => (row as unknown[]).length));
      headers = Array.from({ length: colCount }, (_, i) => `Column${i + 1}`);
      dataRows = rawData;
    }

    // Convert to object array
    const rows: Record<string, unknown>[] = dataRows.map(row => {
      const obj: Record<string, unknown> = {};
      headers.forEach((header, idx) => {
        obj[header] = (row as unknown[])[idx] ?? '';
      });
      return obj;
    });

    return {
      headers,
      rows,
      rawData,
      sheetName,
      totalRows: rows.length
    };
  }

  /**
   * Get file extension
   */
  private getExtension(filename: string): string {
    const match = filename.toLowerCase().match(/\.[^.]+$/);
    return match ? match[0] : '';
  }

  /**
   * Detect CSV delimiter
   */
  private detectDelimiter(content: string, filename: string): string {
    const ext = this.getExtension(filename);

    // TSV files use tab character
    if (ext === '.tsv') {
      return '\t';
    }

    // Analyze first few lines
    const lines = content.split('\n').slice(0, 5);
    const candidates = [',', ';', '\t', '|'];

    let bestDelimiter = ',';
    let bestScore = 0;

    for (const delimiter of candidates) {
      const counts = lines.map(line => line.split(delimiter).length - 1);
      // Consistency score: all rows have same delimiter count
      const isConsistent = counts.every(c => c === counts[0] && c > 0);
      const avgCount = counts.reduce((a, b) => a + b, 0) / counts.length;

      if (isConsistent && avgCount > bestScore) {
        bestScore = avgCount;
        bestDelimiter = delimiter;
      }
    }

    return bestDelimiter;
  }
}

/**
 * Factory function to create parser instance
 *
 * @returns ExcelParser instance
 *
 * @example
 * const parser = createParser();
 * const data = await parser.parse(buffer, { filename: 'data.csv', headers: true });
 */
export function createParser(): ExcelParser {
  return new ExcelParser();
}

/**
 * Shortcut parse function
 *
 * @param buffer - File content
 * @param options - Parse options
 * @returns Parse result
 *
 * @example
 * const data = await parseFile(buffer, { filename: 'report.xlsx' });
 */
export async function parseFile(
  buffer: Buffer,
  options: ParseOptions
): Promise<ParsedData> {
  const parser = createParser();
  return parser.parse(buffer, options);
}
