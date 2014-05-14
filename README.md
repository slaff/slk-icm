Interactive Command line Markup(ICM)

ICM is created to allow you easily create intereactive tutorials for your command
line program similar to the interactive tutorials of Redis (http://try.redis.io) or Docker (https://www.docker.io/gettingstarted/).

Installation
=============
Add the following line in in require compose.json section:

"slk/icm" : "dev-master"

After that in your PHP application you can use the Slk\View\ICMRenderer.

Markup Langage
==============
Prompt
~~~~~~
To prompt the user input you can add in the beginning of a line the following 

>_

To prompt for user output and show also the name of the command line program use:

>$_

The sequence below can be used to check against the user input against expected value:

>$_
==--help

That above two lines will create a prompt with the name of the current program and will expect
from the user to enter --help

You can also use also regular experession for input matching

>$_
==/\s*\-\-help\s*/

The command above will check if the user entered --help with or without trailing spaces.

If the expected input from the user is difficult for him to guess you can add an example. 

>$_
==/\s*command\s+\-\-help\s*/
===command --help

And finally if you want to save the user input you can store it in a variable with a name

>$_
==/\s*command\s+\-\-help\s*/
===command --help
=> input

The above three lines will ask the user for an input, he has to answer with "command --help" or will be shown the 
example at the end and the input of the user will be saved in a variable with the name input.


Command exection
~~~~~~~~
To pass arguments to the current console line program and execute them you can use the following syntax:

$_ xyz

This will execute the in a separate shell "<current command> xyz". The result will be save in a variable with a name "result".

Variables
~~~~~
Variables are describe in the same way that you do in PHP. There is one pre-defined variable $script that contains
the full name of the command line application that is currently executed.

After command execution with $_ the result is saved in $result. 
The input from a promt can be saved in a named variable that can be used later on. 

>$_
==/\s*command\s+\-\-help\s*/
===command --help
=> input

In the example above after these four lines are executed you can get the info by reading $input. 

Page breaks
~~~~~~~~~~~

If you have a line starting with three or more dashes (-) then this will ask the user to press enter to continue.
And if you have three or more > symbols this will ask the user to press enter in order to go on the next page.
Going on the next page in a command line context means to clear the currect screen.
