import path from 'path';
import type { UserConfigExport } from '@tarojs/cli';

const config: UserConfigExport = {
  projectName: 'miniprogram-frontend',
  date: '2024-01-21',
  designWidth: 750,
  deviceRatio: {
    640: 2.34 / 2,
    750: 1,
    375: 2,
    828: 1.81 / 2
  },
  sourceRoot: 'src',
  outputRoot: process.env.TARO_ENV === 'weapp' ? '/workspace/weapp' : 'dist',
  plugins: ['@tarojs/plugin-framework-react'],
  defineConstants: {
    // 注入环境变量，构建时会替换为实际值
    TARO_APP_API_URL: JSON.stringify(process.env.TARO_APP_API_URL || ''),
  },
  copy: {
    patterns: [],
    options: {}
  },
  framework: 'react',
  compiler: {
    type: 'webpack5',
    prebundle: { enable: false }
  },
  cache: {
    enable: false
  },
  alias: {
    '@': path.resolve(__dirname, '..', 'src')
  },
  mini: {
    postcss: {
      pxtransform: {
        enable: true,
        config: {}
      },
      url: {
        enable: true,
        config: {
          limit: 1024
        }
      },
      cssModules: {
        enable: false,
        config: {
          namingPattern: 'module',
          generateScopedName: '[name]__[local]___[hash:base64:5]'
        }
      }
    },
    sassLoaderOption: {
      // Taro UI requires sass-loader configuration
      implementation: require('sass')
    }
  },
  h5: {
    publicPath: '/',
    staticDirectory: 'static',
    esnextModules: ['taro-ui'],
    output: {
      filename: 'js/[name].[hash:8].js',
      chunkFilename: 'js/[name].[chunkhash:8].js'
    },
    miniCssExtractPluginOption: {
      ignoreOrder: true,
      filename: 'css/[name].[hash].css',
      chunkFilename: 'css/[name].[chunkhash].css'
    },
    postcss: {
      autoprefixer: {
        enable: true,
        config: {}
      },
      pxtransform: {
        enable: true,
        config: {
          // H5 uses 750 design width, converts px to rem
          // 1px in code = 1rpx in design = 0.5px on 375px device
        }
      },
      cssModules: {
        enable: false,
        config: {
          namingPattern: 'module',
          generateScopedName: '[name]__[local]___[hash:base64:5]'
        }
      }
    },
    devServer: {
      port: 5173,
      proxy: {
        '/api': {
          target: 'http://localhost:3000',
          changeOrigin: true
        }
      },
      client: {
        overlay: {
          errors: true,
          warnings: false
        }
      }
    }
  }
};

export default config;
