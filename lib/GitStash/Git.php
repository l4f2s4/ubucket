<?php

namespace GitStash;

use GitStash\Exception\InvalidGitObjectException;
use GitStash\Exception\ReferenceNotFoundException;
use GitStash\Git\Blob;
use GitStash\Git\Commit;
use GitStash\Git\Commit2;
use GitStash\Git\Diff;
use GitStash\Git\PrettyFormat;
use GitStash\Git\Tag;
use GitStash\Git\Tree;
use Symfony\Component\Process\Process;

class Git {

    protected $path;
    protected $process;
    protected $pipes;

    function __construct($path)
    {
        $this->path = $path;

        $this->proc = null;
    }

    function __destruct()
    {
        $this->processClose();
    }

    protected function processOpen()
    {
        if ($this->process) {
            return;
        }

        $descriptors = array(
           0 => array("pipe", "r"),
           1 => array("pipe", "w"),
        );

        $this->process = proc_open("git --git-dir=".$this->path." cat-file --batch", $descriptors, $this->pipes);
    }

    protected function processClose()
    {
        if (! $this->process) {
            return;
        }

        fclose($this->pipes[0]);
        fclose($this->pipes[1]);

        proc_close($this->process);
    }

    /**
     * @param $sha
     * @return Object
     */
    function fetchObject($sha)
    {
        list($info, $content) = $this->fetchRawShaData($sha);

        return $this->createObject($info, $content);
    }

    protected function fetchRawShaData($sha) {

        $this->processOpen();

        // Write sha to git-cat-file
        fwrite($this->pipes[0], "$sha\n");

        // Read info back
        do {
            $info = trim(fgets($this->pipes[1]));
        } while (! strlen($info));

        // Read sha, type, size
        $info = array_combine(array('sha', 'type', 'size'), explode(" ", $info));
       // if($info['size'] != 0){
          $content = fread($this->pipes[1], $info['size']);
       // }
       /* else{
          $content = fread($this->pipes[1], '10');
        }*/
         return array($info, $content);

    }

    protected function createObject(array $info, $content)
    {
        switch ($info['type']) {
            case 'blob' :
                return $this->parseBlob($info, $content);
                break;
            case 'commit' :
                return $this->parseCommit($info, $content);
                break;
            case 'tag' :
                return $this->parseTag($info, $content);
                break;
            case 'tree' :
                return $this->parseTree($info, $content);
                break;
        }

        throw new InvalidGitObjectException(sprintf("Cannot create object: Invalid type '%s'", $info['type']));
    }

    protected function parseBlob(array $info, $content)
    {
        return new Blob($info['sha'], $content);
    }

    protected function parseTree(array $info, $content)
    {
        preg_match_all('/([0-7]+) ([^\x00]+)\x00(.{20})/sm', $content, $matches);

        $tree = array();
        foreach (array_keys($matches[0]) as $k) {
            $tree[] = array(
                'perm' => $matches[1][$k],
                'name' => $matches[2][$k],
                'sha' => bin2hex($matches[3][$k]),
            );
        }

        return new Tree($info['sha'], $tree);
    }

    protected function parseCommit(array $info, $content)
    {
        // Parse headers until first empty \n
        $commit = array(
            'tree' => null,
            'parent' => null,
            'committer' => null,
            'author' => null,
            'log' => null,
            'log_details' => null,
        );
        do {
            list($line, $content) = explode("\n", $content, 2);

            if (strlen($line) == 0) break;

            list($type, $type_info) = explode(" ", $line, 2);
            $commit[$type] = $type_info;
        } while (strlen($line));

        // Parse commit log line and details (remainder lines)
        $content = explode("\n", $content, 2);
        $commit['log'] = $content[0];
        $commit['log_details'] = isset($content[1]) ? $content[1] : "";

        return new Commit(
            $info['sha'],
            $commit['tree'],
            $commit['parent'],
            $commit['committer'],
            $commit['author'],
            $commit['log'],
            $commit['log_details']
        );
    }

    protected function parseTag(array $info, $content)
    {
        // Parse headers until first empty \n
        $commit = array(
            'object' => null,
            'type' => null,
            'tag' => null,
            'tagger' => null,
        );
        do {
            list($line, $content) = explode("\n", $content, 2);

            if (strlen($line) == 0) break;

            list($type, $type_info) = explode(" ", $line, 2);
            $commit[$type] = $type_info;
        } while (strlen($line));

        // Parse commit log line and details (remainder lines)
        $content = explode("\n", $content, 2);
        $commit['log'] = $content[0];
        $commit['log_details'] = isset($content[1]) ? $content[1] : "";

        return new Tag(
            $info['sha'],
            $commit['object'],
            $commit['type'],
            $commit['tag'],
            $commit['tagger'],
            $commit['log'],
            $commit['log_details']
        );
    }

    function getRefs($type)
    {
        // Add file system refs
        $it = new \FilesystemIterator($this->path."/refs/".$type, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_PATHNAME);
        $ret = array();
        foreach ($it as $path) {
            $ret[basename($path)] = trim(file_get_contents($path));
        }

        // Add packed refs
        $path = $this->path . "/packed-refs";
        if (is_readable($path)) {
            $refs = file($path);
            foreach ($refs as $line) {
                $line = trim($line);

                // Comment
                if ($line[0] == '#') continue;

                // Annotated tag. Information is found in the previous line as well, so we don't do anything with it
                if ($line[0] == '^') continue;

                list($sha, $ref) = explode(" ", $line, 2);

                $a = explode('/', $ref);
                $refType = $a[1];
                $a = array_splice($a, 2);
                $ref = join('/', $a);
                ;
                if ($refType == $type) {
                    $ret[$ref] = $sha;
                }
            }
        }

        // Sort refs
        ksort($ret);

        return $ret;
    }

    /**
     * Returns the sha the reference points to
     *
     * @param $ref
     * @param string $base
     * @return string
     */
    function refToSha($ref, $base = 'heads') {
        $refs = $this->getRefs('heads');
        if (isset($refs[$ref])) {
            return $refs[$ref];
        }

        $refs = $this->getRefs('tags');
        if (isset($refs[$ref])) {
            return $refs[$ref];
        }

        throw new ReferenceNotFoundException(sprintf("Reference %s' not found", $ref));
    }

    /**
     * Returns a ref that has been packed (ie: located not in the /refs directory, but in the /packed-refs file)
     *
     * @param $wantedRef
     * @return mixed
     */
    protected function findPackedRef($wantedRef) {
        $refs = file($this->path . "/packed-refs");

        foreach ($refs as $line) {
            $line = trim($line);
            if ($line[0] == '#') continue;
            list($sha, $ref) = explode(" ", $line, 2);
            if ($ref == $wantedRef) {
                return $sha;
            }
        }

        throw new ReferenceNotFoundException('Ref $wantedRef not found in packed refs');
    }

    function getTotalCommits()
    {
        exec("git --git-dir=".$this->path." rev-list --all --count", $output);
        $output = join("", $output);

        return $output;
    }

    function getContributors()
    {
        exec("git --git-dir=".$this->path." log --format='%aE %aN' | sort | uniq -c | sort -rn", $output);

        $contributors = array();
        foreach ($output as $line) {
            list($count, $email, $name) = explode(" ", trim($line), 3);
            $contributors[] = array(
                'count' => $count,
                'name' => $name,
                'email' => $email,
            );
        }

        return $contributors;
    }
    function getCommitters($branch)
    {
        exec("git --git-dir=".$this->path." log --format='%cE %cN %cI %H %h %s' ".$branch, $output);

        $commiters = array();
        foreach ($output as $line) {
            list($email, $name, $date,$hash, $shorthash, $message) = explode(" ", trim($line), 6);
            $commiters[] = array(
                'email' => $email,
                'name' => $name,
                'date' => $date,
                'hash' => $hash,
                'Shorthash' => $shorthash,
                'message' => $message,
            );
        }

        return $commiters;
    }

    function getGraph(){
        exec('git --git-dir='.$this->path.' log --graph --date-order --all -C -M -n 100 --date=iso '.'--pretty=format:"B[%d] C[%H] D[%ad] A[%an] E[%ae] H[%h] S[%s]"', $output1);
        $output1 = implode("\n",$output1);
        $output1 = explode("\n", $output1);
        $graphItems = array();

        foreach ($output1 as $row) {
            if (preg_match("/^(.+?)(\s(B\[(.*?)\])? C\[(.+?)\] D\[(.+?)\] A\[(.+?)\] E\[(.+?)\] H\[(.+?)\] S\[(.+?)\])?$/", $row, $output)) {
                if (!isset($output[4])) {
                    $graphItems[] = array(
                        'relation' => $output[1],
                    );
                    continue;
                }
                $graphItems[] = array(
                    'relation' => $output[1],
                    'branch' => $output[4],
                    'rev' => $output[5],
                    'date' => $output[6],
                    'author' => $output[7],
                    'author_email' => $output[8],
                    'short_rev' => $output[9],
                    'subject' => preg_replace('/(^|\s)(#[[:xdigit:]]+)(\s|$)/', '$1<a href="$2">$2</a>$3', $output[10]),
                );
            }
        }

        return $graphItems;
    }


     /**
     * Show the data from a specific commit.
     *
     * @param  string $commitHash Hash of the specific commit to read data
     *
     * @return array  Commit data
     */
    function getCommit($commitHash)
    {
        // exec('git --git-dir='.$this->path.' show --pretty=format:"<item><hash>%H</hash><short_hash>%h</short_hash><tree>%T</tree><parents>%P</parents><author>%aN</author><author_email>%aE</author_email><date>%at</date><commiter>%cN</commiter><commiter_email>%cE</commiter_email><commiter_date>%ct</commiter_date><message><![CDATA[%s]]></message><body><![CDATA[%b]]></body></item>" '.$commitHash,$logs);
        $process = new Process(['git', '--git-dir='.$this->path, 'show', '--pretty=format:"<item><hash>%H</hash><short_hash>%h</short_hash><tree>%T</tree><parents>%P</parents><author>%aN</author><author_email>%aE</author_email><date>%at</date><commiter>%cN</commiter><commiter_email>%cE</commiter_email><commiter_date>%ct</commiter_date><message><![CDATA[%s]]></message><body><![CDATA[%b]]></body></item>"',$commitHash]);
        $process->run();
        $logs = $process->getOutput();
        // $logs = implode($logs);
        $xmlEnd = strpos($logs, '</item>') + 7;
        $commitInfo = substr($logs, 0, $xmlEnd);
        $commitData = substr($logs, $xmlEnd);
        $logs = explode("\n", $commitData);

        // Read commit metadata
        $format = new PrettyFormat();
        $data = $format->parse($commitInfo);
        $commit = new Commit2();
        $commit->importData($data[0]);

        // if ($commit->getParentsHash()) {
        //     $command = 'diff ' . $commitHash . '~1..' . $commitHash;
        //     $proc = new Process(['git', '--git-dir='.$this->path,'diff',$commitHash,'~1..',$commitHash]);
        //     $proc->run();
        //     $logs = $proc->getOutput();
        //     $logs = explode("\n", $logs);
        // }

        $commit->setDiffs($this->readDiffLogs($logs));

        return $commit;
    }

    /**
     * Read diff logs and generate a collection of diffs.
     *
     * @param  array $logs Array of log rows
     *
     * @return array Array of diffs
     */
    function readDiffLogs(array $logs)
    {
        $diffs = array();
        $lineNumOld = 0;
        $lineNumNew = 0;
        foreach ($logs as $log) {
            // Skip empty lines
            if ($log == '') {
                continue;
            }

            if ('diff' === substr($log, 0, 4)) {
                if (isset($diff)) {
                    $diffs[] = $diff;
                }

                $diff = new Diff();
                if (preg_match('/^diff --[\S]+ a\/?(.+) b\/?/', $log, $name)) {
                    $diff->setFile($name[1]);
                }
                continue;
            }

            if ('index' === substr($log, 0, 5)) {
                $diff->setIndex($log);
                continue;
            }

            if ('---' === substr($log, 0, 3)) {
                $diff->setOld($log);
                continue;
            }

            if ('+++' === substr($log, 0, 3)) {
                $diff->setNew($log);
                continue;
            }

            // Handle binary files properly.
            if ('Binary' === substr($log, 0, 6)) {
                $m = array();
                if (preg_match('/Binary files (.+) and (.+) differ/', $log, $m)) {
                    $diff->setOld($m[1]);
                    $diff->setNew("    {$m[2]}");
                }
            }

            if (!empty($log)) {
                switch ($log[0]) {
                    case '@':
                        // Set the line numbers
                        preg_match('/@@ -([0-9]+)(?:,[0-9]+)? \+([0-9]+)/', $log, $matches);
                        $lineNumOld = $matches[1] - 1;
                        $lineNumNew = $matches[2] - 1;
                        break;
                    case '-':
                        $lineNumOld++;
                        break;
                    case '+':
                        $lineNumNew++;
                        break;
                    default:
                        $lineNumOld++;
                        $lineNumNew++;
                }
            } else {
                $lineNumOld++;
                $lineNumNew++;
            }

            if (isset($diff)) {
                $diff->addLine($log, $lineNumOld, $lineNumNew);
            }
        }

        if (isset($diff)) {
            $diffs[] = $diff;
        }

        return $diffs;
    }


    function getAuthorStatistics($branch)
    {
        $process = new Process(['git', '--git-dir='.$this->path,'log','--pretty=format:"%aN||%aE"', $branch]);
        $process->run();
        $logs = $process->getOutput();
        if (empty($logs)) {
            throw new \RuntimeException('No statistics available');
        }

        $logs = explode("\n", $logs);
        $logs = array_count_values($logs);
        arsort($logs);

        foreach ($logs as $user => $count) {
            $user = explode('||', $user);
            $data[] = array('name' => $user[0], 'email' => $user[1], 'commits' => $count);
        }

        return $data;
    }

    function getStatistics($branch)
    {
        // Calculate amount of files, extensions and file size
        $process = new Process(['git', '--git-dir='.$this->path,'ls-tree', '-r' ,'-l', $branch]);
        $process->run();
        $logs = $process->getOutput();
        $lines = explode("\n", $logs);
        $files = array();
        $data['extensions'] = array();
        $data['size'] = 0;
        $data['files'] = 0;

        foreach ($lines as $key => $line) {
            if (empty($line)) {
                unset($lines[$key]);
                continue;
            }

            $files[] = preg_split("/[\s]+/", $line);
        }

        foreach ($files as $file) {
            if ($file[1] == 'blob') {
                $data['files']++;
            }

            if (is_numeric($file[3])) {
                $data['size'] += $file[3];
            }
        }

        $process = new Process(['git', '--git-dir='.$this->path,'ls-tree','-l', '-r', '--name-only', $branch]);
        $process->run();
        $logs = $process->getOutput();
        $files = explode("\n", $logs);
        foreach ($files as $file) {
            if (($pos = strrpos($file, '.')) !== false) {
                $extension = substr($file, $pos);

                if (($pos = strrpos($extension, '/')) === false) {
                    $data['extensions'][] = $extension;
                }
            }
        }

        $data['extensions'] = array_count_values($data['extensions']);
        arsort($data['extensions']);

        return $data;
    }

}
