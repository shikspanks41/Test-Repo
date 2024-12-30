<?php

// Function to execute a shell command and log the output
function logAndExecute($command, $workingDir, $logOutput = true) {
    chdir($workingDir);
    echo "Executing: $command\n";
    $output = shell_exec($command);
    if ($logOutput) {
        echo $output . "\n";
    }
    return $output;
}

// Define the current working directory (current repo directory)
$repositoryPath = getcwd();  // Get the current working directory

// Static file names
$filesToProcess = ['package.json', 'package-lock.json'];

// Get the current time for tag name
$currentTime = date("YmdHis");
$tagName = "tag-$currentTime";
$commitMessage = "Temporarily remove files and create tag";

// Step 1: Temporarily remove files from staging using `git rm --cached`
foreach ($filesToProcess as $file) {
    if (file_exists($repositoryPath . '/' . $file)) {
        $removeCommand = sprintf('git rm --cached %s', escapeshellarg($file));
        logAndExecute($removeCommand, $repositoryPath, false);
        echo "$file temporarily removed from staging.\n";
    } else {
        echo "File $file does not exist in the repository!\n";
    }
}

// Step 2: Commit the removal of the files
$commitCommand = sprintf('git commit -m "%s"', escapeshellarg($commitMessage));
logAndExecute($commitCommand, $repositoryPath);

// Step 3: Create a tag with the current timestamp
$tagCommand = sprintf('git tag -a %s -m "Tag created at %s"', escapeshellarg($tagName), $currentTime);
logAndExecute($tagCommand, $repositoryPath);

// Step 4: Re-add the files to staging (instead of restoring)
foreach ($filesToProcess as $file) {
    if (file_exists($repositoryPath . '/' . $file)) {
        $addCommand = sprintf('git add %s', escapeshellarg($file));
        logAndExecute($addCommand, $repositoryPath, false);
        echo "$file added back to staging.\n";
    } else {
        echo "File $file does not exist in the repository!\n";
    }
}

// Step 5: Commit the restoration of the files
$restoreCommitCommand = 'git commit -m "Restore files after temporary removal"';
logAndExecute($restoreCommitCommand, $repositoryPath);

// Step 6: Push changes to the main branch and the tag
$pushCommand = sprintf('git push origin main %s', escapeshellarg($tagName));
logAndExecute($pushCommand, $repositoryPath);

echo "Script finished successfully!\n";

?>
