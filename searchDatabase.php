<?php

function getResults($terms)
{
    //0 is none, 1 is case, 2 is partial, 3 is both
    $dbhost = DbHost;
    $dbuser = Username;
    $dbpass = Password;
    $db = Username;
    $conn = new mysqli($dbhost, $dbuser, $dbpass,$db) or die("Connect failed: %s\n". $conn -> error);

    $starttime = microtime(true);

    $checkVal = $_POST["value"];
    $searchVal = explode(" ",$terms);
    $results = array();
    foreach($searchVal as $term){
        if(trim($term) != ""){
            if($checkVal == 0){
                $term = '"' . $term . '"';
                $searchQuery = $conn->query("SELECT page.url, page.title, page.description, page_word.freq
                FROM page, word, page_word
                WHERE page.pageId = page_word.pageId
                AND word.wordId = page_word.wordId
                AND word.wordName = $term
                ORDER BY freq");
    
            }
            elseif($checkVal == 1){
                $term = '"' . $term . '"';
                $searchQuery = $conn->query("SELECT page.url, page.title, page.description, page_word.freq
                FROM page, word, page_word
                WHERE page.pageId = page_word.pageId
                AND word.wordId = page_word.wordId
                AND Upper(word.wordName) = Upper($term)
                ORDER BY freq");
            }
            elseif($checkVal == 2){
                $term = '"%' . $term . '%"';
                $searchQuery = $conn->query("SELECT page.url, page.title, page.description, page_word.freq
                FROM page, word, page_word
                WHERE page.pageId = page_word.pageId
                AND word.wordId = page_word.wordId
                AND word.wordName LIKE $term
                GROUP BY url
                ORDER BY freq");
            }
            elseif($checkVal == 3){
                $term = '"%' . $term . '%"';
                $searchQuery = $conn->query("SELECT page.url, page.title, page.description, page_word.freq
                FROM page, word, page_word
                WHERE page.pageId = page_word.pageId
                AND word.wordId = page_word.wordId
                AND Upper(word.wordName) LIKE Upper($term)
                GROUP BY url
                ORDER BY freq");
            }
        }

        if($searchQuery->num_rows > 0) {
            while($r = mysqli_fetch_array($searchQuery, MYSQLI_ASSOC)){
                $results[] = $r;
            }
        }
    }

    $count = count($results);
    $endtime = microtime(true);
    $time = $endtime - $starttime;
    $terms = '"' . $terms . '"';
    $insertSearch = "INSERT INTO search (terms, count, timeToSearch)
        VALUES($terms, $count, $time)";
    mysqli_query($conn, $insertSearch);

    print json_encode($results);
    $conn -> close();
}

$terms = $_POST["search"];
getResults($terms);
