<?php

interface GitHubRunner
{
   function run ( GitHub $git );
}

class GitHubRefresh implements GitHubRunner
{
   function run ( GitHub $git )
   {
      $lastTag = $git->getLastTag();
      $remote  = $git->getRemoteName();
      $branch  = $git->getBranchName();
      $git->cmd("git push --delete {$remote} {$lastTag}");
      $git->cmd("git tag --delete {$lastTag}");
      $git->cmd("git tag {$lastTag} {$branch}");
      $git->cmd("git push {$remote} {$branch}");
      $git->cmd("git push {$remote} --tags");
   }
}

class GitHub
{
   protected   $cmds;

   function __construct ( GitHubRunner $runner )
   {
      $this->cmds = array();
      $runner->run($this);
   }

   function execute ()
   {
      foreach ( $this->cmds as $cmd )
      {
         print "$cmd\n";
      }

      print "\n";
      do
      {
         $resp = readline('Execute this commands [Yy]es/[Nn]o ? ');
         if ( in_array(strtoupper($resp[0]),array('Y','N')) ) break;
      }
      while (1);

      if ( strtoupper($resp[0]) === 'N' ) exit("\e[33mCanceled.\e[0m\n");

      reset($this->cmds);
      foreach ( $this->cmds as $cmd ) print shell_exec($cmd) . "\n";

      print("\e[32mDone\e[0m\n");
   }

   function cmd ( $cmd )
   {
      array_push($this->cmds, $cmd);
   }

   function getLastTag ()
   {
      return trim(shell_exec('git describe --tags --abbrev=0'));
   }

   function getRemoteName ()
   {
      return trim(shell_exec('git remote'));
   }

   function getBranchName ()
   {
      return trim(ltrim(shell_exec('git branch'),' *'));
   }
}

(new GitHub(new GitHubRefresh()))->execute();