<?php
/*
   This script update the composer.json version tag, and create
   a README.md with corrected version of the software.

   ./templates/README.md must exists with `{version}` template variable where needed.
 */
define('UPDATE_COMPOSER', true);
define('UPDATE_README', true);

class Backup
{
   protected $date;

   function __construct()
   {
      $this->date = date('Y-m-d Hi');
   }

   function create ( $filename )
   {
      $dir = dirname($filename) . DIRECTORY_SEPARATOR;
      list($name, $extensions) = explode('.', basename($filename), 2);
      $backup = "$dir$name {$this->date}.$extensions";
      copy($filename, $backup);
   }
}

function prompt ( string $message, &$response )
{
   print $message . PHP_EOL;
   print '> ';
   $response = trim(fgets( STDIN ));
}

while (1)
{
   prompt('Updating the version software. Continue [Yes|no] ?', $answer);
   switch ( strtolower($answer) )
   {
      case 'n':
      case 'no':
         exit("Bye bye...\n");

      case '':
      case 'y':
      case 'yes':
         break 2;
   }
}

$contents = file_get_contents('./composer.json');
$json = json_decode($contents);

if ( ! property_exists($json, 'version') )
   exit("\e[33mPlease, add the property `version` in the composer.jon file\e[0m\n");

prompt("Actual version: {$json->version}, enter the new one", $version);

$backup = new Backup();

if ( UPDATE_COMPOSER )
{
   // Create a backup of composer.json
   $backup->create('./composer.json');

   // Update composer.json
   $json->version = $version;
   file_put_contents("./composer.json", json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

if ( UPDATE_README )
{
   if ( file_exists('./templates/README-template.md') )
   {
      $contents = file_get_contents('./templates/README-template.md');
      $contents = preg_replace('/{\s*version\s*}/', $version, $contents);

      // Create a backup of README.md
      $backup->create('./README.md');

      // Update README.md
      file_put_contents('./README.md', $contents);
   }
   else
      exit("\e[33mPlease, add the template `./templates/README-template.md`\e[0m\n");
}

print "\e[32mDone.\e[0m\n";