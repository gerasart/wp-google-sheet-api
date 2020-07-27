const WebpackSftpClient = require('webpack-ftp-upload-plugin');
// const filewatcherPlugin = require("filewatcher-webpack-plugin");
const atob = require('atob');

module.exports = function (PATHS, folder) {
  var ftp_project_dir = process.env.PROJECT_NAME + '/www/';
  // var relPath = gulpPath.toRelative(process.env.PROJECT_NAME, distPath);
  var relPath = PATHS.relDist;

  if ( process.env.EXCLUDE_WPAPP > 0 ) {
    relPath = relPath.replace(`wp-app${PATHS.sep}`, '');
  }

  var ftp_options = {
    host: process.env.FTP_HOST,
    port: process.env.FTP_PORT,
    username: process.env.FTP_USER,
    password: atob(process.env.FTP_PASS),
    local: PATHS.dist + folder,
    path: process.env.FTP_PATH + ftp_project_dir + relPath + folder,
  };

  // console.log(ftp_options);

  return {
    plugins: [
      new WebpackSftpClient(ftp_options),
    ]
  }
};
