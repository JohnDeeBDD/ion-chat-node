import commonjs from '@rollup/plugin-commonjs';

export default {
  input: 'src/etm.js',
  output: {
    file: 'src/ETM/etm.js',
    format: 'cjs'
  },
  plugins: [commonjs()]
};