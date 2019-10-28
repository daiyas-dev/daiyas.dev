const SerialPort = require('serialport');
const { Readline } = SerialPort.parsers;

const port = new SerialPort('COM10');
const parser = port.pipe(new Readline({ delimiter: '\n' }));

parser.on('data', data => console.log(data));