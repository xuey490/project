import type { UserConfigExport } from '@tarojs/cli';

export default {
  logger: {
    quiet: false,
    stats: true
  },
  mini: {},
  h5: {
    devServer: {
      client: {
        overlay: {
          errors: true,
          warnings: false  // 禁用警告覆盖层，只显示真正的错误
        }
      }
    }
  }
} satisfies UserConfigExport;
