// npx spack entry=/src/ETM/js/etm.js output=/src/ETM
// npx spack entry=/src/ETM/recurring-subscriptions.js_src/recurring-subscriptions.js output=/src/ETM

const { config } = require('@swc/core/spack')

const args = process.argv;

let entryPoint = "must have the format 'entry=xxx' on the command line";
let outputDir = "must have the format 'output=xxx' on the command line"
args.forEach(function(arg){
  if((arg.slice(0,6)) == "entry=" ){
    arg = arg.substring(6);
    entryPoint = arg;
  }
  if((arg.slice(0,7)) == "output=" ){
    arg = arg.substring(7);
    outputDir = arg;
  }
});
//let entryPointName = returnFileNameFromFullPathWithoutFileExtension(entryPoint);
let configFile = {
  entry: {
    [returnFileNameFromFullPathWithoutFileExtension(entryPoint)]: __dirname + entryPoint,
  },
  output: {
    path: __dirname + outputDir
  },
  module: {},
  //Enable minification
};

/**
 * Extracts the first part of the file name (without the extension) from a full file path.
 *
 * @param {string} file - The full file path.
 * @returns {string} The first part of the file name without the extension.
 */
function returnFileNameFromFullPathWithoutFileExtension(file) {
  // Split the file path using the directory separator (e.g., '/' or '\') based on the OS
  const pathParts = file.split(/[\\/]/);

  // The last part of the split will be the file name with the extension
  const fileNameWithExtension = pathParts[pathParts.length - 1];

  // Split the file name using the dot separator to get the parts before and after the extension
  const fileNameParts = fileNameWithExtension.split('.');

  // The first part of the split will be the file name without the extension
  const fileName = fileNameParts[0];

  return fileName;
}


module.exports = config(configFile);
