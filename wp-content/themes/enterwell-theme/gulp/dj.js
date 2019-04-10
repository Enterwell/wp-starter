// Imports
const https = require('https');

/**
 * Prints the dad joke.
 *
 * @param {function} [printerFn=console.log] - print function
 */
function printDadJoke(printerFn = console.log) {
  // Makes a request
  const req = https.request({
    hostname: 'icanhazdadjoke.com',
    method: 'GET'
  },
  (resp) => {
    // Creates the data
    let data = '';

    // A chunk of data has been recieved.
    resp.on('data', (chunk) => {
      data += chunk;
    });

    // The whole response has been received. Print out the result.
    resp.on('end', () => {
      printerFn(data);
      printerFn('---------');
    });
  });

  // Sets the headers
  req.setHeader('accept', 'text/plain');
  req.setHeader('User-agent', 'Enterwell web starter');

  // Sends the request
  req.end();
}

// Exports
module.exports = {
  printDadJoke
};
