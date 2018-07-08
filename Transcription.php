<?php
// curl -X POST -u "username":"password"
// --header "Content-Type: audio/ogg" --data-binary @audio-file.ogg
// "https://stream.watsonplatform.net/speech-to-text/api/v1/recognize"

// Use this class to send a group of audio files to IBM Watson for transcription.
class Transcription {
  private $folder = "";
  private $files = array(); // The files from the folder.
  private $username = "[your username]";
  private $password = "[your password]";
  private $URL = "https://stream.watsonplatform.net/speech-to-text/api/v1/recognize";
  private $header = array("Content-Type: audio/ogg"); 

  public function load($folder) {
    $this->folder = $folder;
    $this->files = glob("{$this->folder}*.ogg"); 
    $number = count($this->files);
    echo "Loading $number ogg file(s) from $folder...\n";
  }
  public function transcribe() {
    $ch = curl_init(); // Create cURL resource.
    echo "Setting up cURL...\n";
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_USERPWD, "$this->username:$this->password");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
    curl_setopt($ch, CURLOPT_URL, $this->URL); // Set URL.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // Return the transfer as a string.
    foreach($this->files as $filePath) {
      $fileContents = file_get_contents($filePath);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContents);
      echo "Executing cURL...\n";
      $output = curl_exec($ch); // $output contains the output string.
      $output = json_decode($output);
      $statements = "";
      foreach($output->results as $result) {
        // Remove any whitespace from right side, and then add a period and a space.
        // Build statements string with individual statements. 
        $statements .= rtrim($result->alternatives[0]->transcript) . ". ";
      }
      echo "Writing $statements to file...\n";
      file_put_contents("$filePath.txt", $statements);
    }
    curl_close($ch); 
    echo "cURL closed...\n";
  }
}
