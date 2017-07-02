/**
 * @file get mock data
 * @author leon <ludafa@outlook.com>
 */

/* eslint-disable no-console, fecs-no-require */

// throw new Error(JSON.stringify(process.argv));

const path = require('path');
const fs = require('fs');

let componentPath = path.normalize(process.argv[2]);
let mockFilePath = path.join(
    path.dirname(componentPath),
    `${path.basename(componentPath, '.atom.php')}.mock.js`
);

if (!fs.existsSync(mockFilePath)) {
    console.log(JSON.stringify({}));
    return;
}

try {
    console.log(JSON.stringify(require(mockFilePath)));
}
catch (e) {
    console.error(e);
    process.exit(1);
}
