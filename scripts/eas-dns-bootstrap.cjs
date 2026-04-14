const dns = require('dns');

// Force Node-based CLIs (like EAS) to resolve via public DNS when local resolver fails.
dns.setServers(['8.8.8.8', '1.1.1.1']);
