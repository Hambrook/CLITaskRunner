#CLITaskRunner
######By Rick Hambrook
-----

Run a CLI command in the background and get updates via callbacks on events including buffer and line updates. You can subscribe to any writable stream and get callbacks triggered for new lines or on each buffer update.

##Example
```php
$cmd = "mysqldump -uroot -proot -v dbname | mysql -uroot -proot dbname2";
$CLITR = new \Hambrook\CLITaskRunner($cmd);
$CLITR->onLine(
	"stdErr",
	function($v) {
		echo "\nCopying table structure: ".$v
	},
	"/\-\- Retrieving table.*? ([^ ]+)\.\.\./"
);
$CLITR->onLine(
	"stdErr",
	function() {
		echo "\nCopying table rows..."
	},
	"/\-\- Retrieving rows/"
);
$CLITR->process();
```

####Why use this...
I built this because I use `mysqldump` and `rsync` from within a CLI application and wanted to customise the output from those commands. It couldn't be done with a simple `popen` because `mysqldump` puts its verbose output into the `stdErr` stream instead of `stdOut`. Then, naturally, I made it as versatile as possible.

####When you could use this?
When you need to run a CLI command and be notified when specific output occurs on a particular stream.

##Who is it for?
Probably mostly CLI application developers, but there will be usecases I haven't thought of.

##Testing
_Coming soon._

##Feedback
Tell me if you loved it. Tell me if you hated it. Tell me if you used it and thought "meh". I'm keen to hear your feedback.

##Contributing
Feel free to fork this project and submit pull requests, or even just request features via the issue tracker. Please be descriptive with pull requests and match the existing code style.

##Roadmap
* Add documentation
* Add unit tests
* Add composer support
* Add any other standard documentation that should be included
* _If you have an idea, [let me know](mailto:rick@rickhambrook.com)._

##License
Copyright &copy; 2015 Rick Hambrook

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.