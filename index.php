<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bible search tool</title>
<style type="text/css">

@font-face {
    font-family: "Code New Roman";
    src: url("Code New Roman.woff");
}

* { font-family: "Code New Roman"; }

.tooltip {
    position: relative;
    display: inline-block;
    border-bottom: 1px dotted black;
}

.tooltip .tooltiptext {
    visibility: hidden;
    width: 120px;
    background-color: black;
    color: #fff;
    text-align: center;
    border-radius: 6px;
    padding: 5px 0;

    /* Position the tooltip */
    position: absolute;
    z-index: 1;
    bottom: 100%;
    left: 50%;
    margin-left: -60px;
}

.tooltip:hover .tooltiptext { visibility: visible; }

body {
    background-image: url("paper-antique-seamless.jpg");
    background-repeat: repeat;
    background-color: #f0e8bd /* #d9cf98 */;
    color: #000000;
    font-size: 16px;
    display: grid;
    grid-template-columns: auto 0px;
}

p {
    text-align: center;
    font-size: 18px;
    font-weight: bold;
}

/*span { font-size: 16px; }*/

hr { width: 50%; }

#search_help { font-size: 12px; }

#search_form {
    margin-left:   5%;
    margin-right:  5%;
    margin-top:    1%;
    margin-bottom: 1%;
    display:       block;
    outline-style: hidden;
    line-height:   1.5;
    outline-width: 1px;
    /*outline-offset: 20px;*/
    /*box-shadow: 3px 3px;*/
    padding: 20px;
    border-radius: 30px;
    background-color: rgba(240, 232, 189, 0.30);
}

.submit_button {
    font-size: 16px;
    padding: 5px;
    border-radius: 10px;
    border: none;
    background-color: #fff;
}

.submit_button:hover { box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19); }

input.search_criteria {
    font-size: 16px;
    padding: 5px;
    border-radius: 10px;
    border: none;
    width: 100%;
    background-color: #fff;
}

td {
    text-align: center;
    vertical-align: center;
}

table {
    text-align: center;
    margin: auto;
}

/*
table, th, tr, td {
    border: 1px solid;
}
*/

span.highlight { background-color: rgba(247, 243, 5, 1); /* bright yellow */ }

a {
    text-decoration: none;
    color: black;
}

/*
span.verse {
    padding-left: 1em;
    line-height: 1;
    padding-left: 1.5em;
    text-indent:-1.5em;
    padding-left: 1.5em;
}
*/

a.top {
  text-decoration: none;
  padding: 15px;
  /*font-family: sans-serif;*/
  color: #000;
  background: #fff;
  border-radius: 50px;
  position: sticky;
  bottom: 10px;
  place-self: end;
  margin-top: 100vh;
  white-space: nowrap;
  font-size: 20px;
}

</style>
</head>
<body>
<div id="search_form">

<form action="" method="post" name="search_form" enctype="text/plain accept-charset="UTF-8">

<table>
    <tr>
        <td colspan="4">
            <a href="/">King James Version (Cambridge)</a>
        </td>
    </tr>
    <tr>
        <td colspan="4">
            <input type="text" name="criteria" class="search_criteria" value="" maxlength=255 autofocus autocomplete="off">
        </td>
    </tr>
    <tr>
        <td>
            <input type="checkbox" name="exact_matches" checked>Exact
        </td>
        <td>
            <input type="checkbox" name="partial_matches"><a href="#partial_matches">Partial</a>
        </td>
        <td>
            <input type="checkbox" name="near_matches"><a href="#near_matches">Near</a>
        </td>
        <td>
            <input type="checkbox" name="fuzzy_matches"><a href="#fuzzy_matches">Fuzzy</a>
        </td>
    </tr>
    <tr>
        <td colspan="4">
            <input type="checkbox" name="case_sensitive"><a>Case-sensitive</a>
        </td>
    </tr>
    <tr>
        <td colspan="4">
            <input type="submit" name="submit_button" class="submit_button" value="Search">
        </td>
    </tr>
</table>

<?php

# Set debug to false to disable PHP error reporting and other debug features
$debug = true;

if ($debug) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

# Set options based on form checkboxes
if (isset($_POST['submit_button'])) {

    if ($debug) {
        $start_time = microtime(true);
    }

    # Get the text to search for from the criteria input field
    $search_for = $_POST['criteria'];

    # Get option values from the checkboxes
    if (isset($_POST['exact_matches'])) {
        $exact_matches_option = true;
    } else {
        $exact_matches_option = false;
    }

    if (isset($_POST['partial_matches'])) {
        $partial_matches_option = true;
    } else {
        $partial_matches_option = false;
    }

    if (isset($_POST['near_matches'])) {
        $near_matches_option = true;
    } else {
        $near_matches_option = false;
    }

    if (isset($_POST['case_sensitive'])) {
        $case_sensitive = "";
    } else {
        $case_sensitive = "i";
    }

    if (isset($_POST['fuzzy_matches'])) {
        $fuzzy_matches_option = true;
    } else {
        $fuzzy_matches_option = false;
    }

    # if the search criteria is too short, display an error and don't do anything
    if (strlen(trim($search_for)) < 1) {
        ExitWithException("Search criteria \"$search_for\" is too short.");
    }

    # if none of the options for results is checked
    if (!isset($_POST['exact_matches']) && !isset($_POST['partial_matches']) && !isset($_POST['near_matches']) && !isset($_POST['fuzzy_matches'])) {
        ExitWithException("You must enable at least one search method to get any results.");
    }

    # TODO finish
    # If a potential range of books or verses is supplied, switch to only exact match mode
    # 1 cor 1:3,4
    # 1 cor 1:3-6
    # 1 cor 1-4
    if (preg_match("/[0-9]{1,3}:[0-9]{1,3}[,-][0-9]{1,3}|[0-9]{1,3}\-[0-9]{1,3}/", $search_for)) {
        echo "Range detected. Support for ranges has not yet been fully implemented.<br>";
        $exact_matches_option = true;
        $partial_matches_option = false;
        $near_matches_option = false;
        $fuzzy_matches_option = false;
        exit;
    }


    # Bible text used to search.
    # Each line follows the following format:
    # Book <one_space> Book#:Verse# <one_space> <verse (square brackets for italics)>
    $bible_text = "KJV-Cambridge_UTF-8_notes_and_italics_removed_psalmstitles_ule.txt";

    # expand book name abbreviations into their names in the bible text used to search
    # eg. ex => Exodus, gen => Genesis
    $search_for = ExpandAbbreviatedBookNames($search_for);

    # Create a separate variable with escaped special characters for use as a regex pattern
    # . \ + * ? [ ^ ] $ ( ) { } = ! < > | : - #
    $search_for_preg_quoted = preg_quote($search_for);

    # NOTE preg_match_all can be used to find out if multiple search criteria are given (eg. ex 1, gen 5:31-34)(eg. ex 1; gen 4, lev 19:1-19)

    # Variable(s) for stats
    $lines_checked = 0;

    #### EXACT WORD/PHRASE MATCHES
    if ($exact_matches_option) {

        # how many exact matches were found
        $exact_match_count = 0;

        # variable containing pre-formatted results to be displayed
        $exact_match_results = "";

        # search for exact matches
        # NOTE variables are passed by reference
        ExactMatchCaseInsensitive($exact_match_count, $exact_match_results);

        if ($exact_match_count > 0) {

            # exactly 1
            if ($exact_match_count == 1) {
                echo "<br><hr><p>Found $exact_match_count exact match for \"$search_for\"</p><hr>";
            }
            # More than 1
            if ($exact_match_count > 1) {
                echo "<br><hr><p>Found $exact_match_count exact matches for \"$search_for\"</p><hr>";
            }

            echo $exact_match_results;

        } else {
            echo "<br><hr><p>No exact matches found for \"$search_for\"</p><hr>";
        }
    }
    ####

    #### PARTIAL WORD MATCHES
    # Must be enabled via form checkbox
    if ($partial_matches_option) {

        #disabled for phrases
        #if ((count(explode(" ", $search_for))) == 1) {
        $partial_match_count = 0;
        $partial_match_results = "";
        PartialMatchCaseInsensitive($partial_match_count, $partial_match_results);
        if ($partial_match_count > 0) {

            # exactly 1
            if ($partial_match_count == 1) {
                echo "<br><hr><p id=\"partial_matches\">Found $partial_match_count partial word match for \"$search_for\"</p><hr>";
            }
            # More than 1
            if ($partial_match_count > 1) {
                echo "<br><hr><p id=\"partial_matches\">Found $partial_match_count partial word matches for \"$search_for\"</p><hr>";
            }

            echo $partial_match_results;

        } else {
            echo "<br><hr><p id=\"partial_matches\">No partial word matches found for \"$search_for\"</p><hr>";
        }
        #}
    }
    ####

    #### NEAR PHRASE MATCHES
    # Must be enabled via form checkbox
    if ($near_matches_option) {
        # near matches require 4 or more words
        if ((count(explode(" ", $search_for))) >= 4) {
            $near_match_count = 0;
            $near_match_results = "";
            NearMatchCaseInsensitive($near_match_count, $near_match_results);

            if ($near_match_count > 0) {
                # exactly 1
                if ($near_match_count == 1) {
                    echo "<br><br><hr><p id=\"near_matches\">Found $near_match_count near phrase match for \"$search_for\"</p><hr>";
                }
                # More than 1
                if ($near_match_count > 1) {
                    echo "<br><hr><p id=\"near_matches\">Found $near_match_count near phrase matches for \"$search_for\"</p><hr>";
                }

                echo $near_match_results;

            } else {
                echo "<br><hr><p id=\"near_matches\">No near phrase matches found for \"$search_for\"</p><hr>";
            }
        }
    }
    ####

    ####
    # Must be enabled via form checkbox
    if ($fuzzy_matches_option) {

    }
    ####

    if ($debug) {
        $end_time = microtime(true);
        echo "<br><hr><p style=\"font-weight: normal; font-size: 14px;\">$lines_checked lines checked in ", round($end_time - $start_time, 6), " seconds</p><br>";
    }
}

################################################################################

function ExactMatchCaseInsensitive(&$exact_match_count, &$exact_match_results) {

    global $debug, $bible_text, $lines_checked, $search_for, $search_for_preg_quoted, $case_sensitive;

    # open the file of the text to search read only
    $handle = @fopen($bible_text, "r");

    if ($handle) {

        # get one line at a time from the file
        while (($line = fgets($handle)) !== false) {
            if ($debug) { $lines_checked += 1; }

            # Remove brackets originally used to indicate italics
            $new_line = str_replace("[", "", $line);
            $new_line = str_replace("]", "", $new_line);

            #echo "checking \"$new_line\" for \"$search_for_preg_quoted\"<br>";

            # if line contains the pattern
            if (preg_match("/\b$search_for_preg_quoted\b/$case_sensitive", strip_tags($new_line)) === 1) {
                #echo "found a match<br>";

                # replace newline (not visible as html) with a break (is visable as html)
                # keep newlines during debugging (easier to read page source)
                if ($debug) {
                    $new_line = str_replace("\n", "<br>\n", $new_line);
                } else{
                    $new_line = str_replace("\n", "<br>", $new_line);
                }

                #$search_array = explode(" ", $search_for);
                #foreach ($search_array as $word) {
                #    $new_line = HighlightNoTooltip($new_line, $word);
                #}
                $new_line = HighlightNoTooltip($new_line, $search_for);

                $new_line = BoldBookName($new_line);

                $exact_match_results = $exact_match_results . $new_line;

                # Increment the match counter by 1
                $exact_match_count += 1;
            }
        }
    }
    fclose($handle);
}

################################################################################

function PartialMatchCaseInsensitive(&$partial_match_count, &$partial_match_results) {

    global $debug, $bible_text, $lines_checked, $search_for, $search_for_preg_quoted, $case_sensitive;

    $search_array = explode(" ", $search_for);
    $search_word_count = count($search_array);

    # open the file of the text to search read only
    $handle = @fopen($bible_text, "r");

    if ($handle) {

        # get one line at a time from the file
        while (($line = fgets($handle)) !== false) {
            if ($debug) { $lines_checked += 1; }

            # Remove brackets originally used to indicate italics
            $new_line = str_replace("[", "", $line);
            $new_line = str_replace("]", "", $new_line);

            # if line contains the exact pattern
            if (preg_match("/\b$search_for_preg_quoted\b/$case_sensitive", strip_tags($new_line)) === 1) {
                # skip exact whole-word/whole-phrase matches
            } else {
                # check if the word or all words partially match the line
                $word_counter = 0;
                foreach ($search_array as $word) {
                    $word_preg_quoted = preg_quote($word);

                    # if the whole word is not found and
                    # if a partial match for a word is found increment the counter
                    if (preg_match("/\b$word_preg_quoted\b/$case_sensitive", strip_tags($new_line)) === 1) {
                        #nothing
                    } else {
                        if (preg_match("/$word_preg_quoted/$case_sensitive", strip_tags($new_line)) === 1) {
                            $word_counter += 1;
                        }
                    }

                    # if the counter reaches the number of words for this line
                    # if all words have a partial match for this line it becomes a result to be shown
                    if ($word_counter == $search_word_count) {

                        # replace newline (not visible as html) with a break (is visable as html)
                        # keep newlines during debugging (easier to read page source)
                        if ($debug) {
                            $new_line = str_replace("\n", "<br>\n", $new_line);
                        } else {
                            $new_line = str_replace("\n", "<br>", $new_line);
                        }

                        #foreach ($search_array as $word) {
                        #    $new_line = HighlightNoTooltip($new_line, $word);
                        #}
                        $new_line = HighlightNoTooltip($new_line, $search_for);

                        $new_line = BoldBookName($new_line);

                        $partial_match_results = $partial_match_results . $new_line;

                        # Increment the match counter by 1
                        $partial_match_count += 1;
                    }
                }
            }
        }
    }
fclose($handle);
}

################################################################################

function NearMatchCaseInsensitive(&$near_match_count, &$near_match_results) {

    global $debug, $bible_text, $lines_checked, $search_for, $search_for_preg_quoted, $case_sensitive;

    # open the file of the text to search read only
    $handle = @fopen($bible_text, "r");

    if ($handle) {

        # get one line at a time from the file
        while (($line = fgets($handle)) !== false) {
            if ($debug) { $lines_checked += 1; }

            $replacement_count = 0;

            # Remove brackets originally used to indicate italics
            $new_line = str_replace("[", "", $line);
            $new_line = str_replace("]", "", $new_line);

            # if there is no exact match
            if (preg_match("/\b$search_for_preg_quoted\b/$case_sensitive", strip_tags($new_line)) === 1) {
                # do nothing
            } else {

                # replace newline (not visible as html) with a break (is visable as html)
                # keep newlines during debugging (easier to read page source)
                if ($debug) {
                    $new_line = str_replace("\n", "<br>\n", $new_line);
                } else {
                    $new_line = str_replace("\n", "<br>", $new_line);
                }

                $new_line = CheckForConsecutiveSubstrings($search_for, $new_line, $replacement_count);

                if ($replacement_count > 0) {

                    $new_line = BoldBookName($new_line);

                    $near_match_results = $near_match_results . $new_line;

                    $near_match_count += 1;
                }
            }
        }
    }

    fclose($handle);
}

################################################################################

function CheckForConsecutiveSubstrings($needle, $haystack, &$replacement_count) {

    global $case_sensitive;

    $search_array = explode(" ", $needle);
    $search_word_count = count($search_array);
    $new_line = $haystack;

    # 4 words or more only
    if ($search_word_count >= 4) {

        # subtract one word from the end at a time until the minimum of 2 words is reached and check if it is in haystack
        for($i = ($search_word_count - 1); $i >= 3; $i--) {
            $check_for = implode(" ", array_slice($search_array, 0, $i));
            # make check_for safe for use as a regex pattern
            $pattern = preg_quote($check_for);
            if (preg_match("/\b$pattern\b/$case_sensitive", strip_tags($new_line)) === 1) {
                $new_line = HighlightNoTooltip($new_line, $check_for);
                $replacement_count += 1;
            }
        }

        # subtract one word from the beginning at a time until the minimum of 2 words is reached and check if it is in haystack
        for($i = 1; $i < ($search_word_count - 2) ; $i++) {
            $check_for = implode(" ", array_slice($search_array, $i, $search_word_count));
            # make check_for safe for use as a regex pattern
            $pattern = preg_quote($check_for);
            if (preg_match("/\b$pattern\b/$case_sensitive", strip_tags($new_line)) === 1) {
                $new_line = HighlightNoTooltip($new_line, $check_for);
                $replacement_count += 1;
            }
        }
    }
    return $new_line;
}

################################################################################

function HighlightNoTooltip($line, $term) {

    #TODO needs fixed and finished
    #return $line;

    # Don't highlight booknames
    # support for "Mt 20"
#    $checkterm = substr($term, 0, strpos($term, ' '));
    # support for "Mt", "mark"
#    if ($checkterm == '') { $checkterm = $term; }

#    echo "checkterm: $checkterm<br>";

#    $checkline = substr($line, 0, strlen($checkterm));
#    echo "subline:   ", substr($line, 0, strlen($checkterm)), "<br>";

#    if (IsABookName($checkterm)) {
#        if (IsABookName($checkline)) {
#            return $line;
#        }
#    }

    $highlight = "<span class=\"highlight\">";

    #echo "Highlighting before: \"$term\" in \"$line\"<br>";

    # get the position where the string to be highlighted begins
    $term_beg = stripos(strip_tags($line), $term);

    #echo "term_begin: $term_beg<br>$line<br>$term<br>";

    # get the position where the highlighting should end
    $term_end = $term_beg + strlen($term) + strlen($highlight);

    #echo "term_end: $term_end<br>";

    $line = substr_replace($line, $highlight, $term_beg, 0);
    $line = substr_replace($line, "</span>", $term_end, 0);

    #echo "Highlighting after: \"$term\" in \"$line\"<br>";

    return $line;
}

################################################################################

function BoldBookName($line) {
#<span class="highlight">Genesis</span> 40:<span class="highlight">1</span> And it came to pass after these things, <i> that </i> the butler of the king of Egypt and <i> his </i> baker had offended their lord the king of Egypt.<br>

    #TODO needs fixed and finished
    #return $line;

    $space_after_colon_pos = strpos($line, ":");
    $space_after_colon_pos = strpos($line, " ", $space_after_colon_pos);

    $book = substr($line, 0, $space_after_colon_pos);
    # Replace only the first occurance

    #$line = substr_replace($line, "<br><b>$book</b><br><div class=\"verse\">", 0, strlen($book));
    # OR
    $line = substr_replace($line, "<b>$book</b><span class=\"verse\">", 0, strlen($book));

    # THEN
    $line = substr_replace($line, "</span>", strlen($line));

    # plain
    #$line = substr_replace($line, "<b>$book</b>", 0, strlen($book));

    return $line;
}

################################################################################

function SpellCheck($word_to_check) {
    if (trim($word_to_check) == "") { return false; }
    echo "checking dic for $word_to_check<br>";
    $dictionary = "english_UTF8.dic";
    $handle = @fopen($dictionary, "r");
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            if (stripos($line, $word_to_check) !== false) {
                return true;
            }
        }
        return false;
    } else {
        return false;
    }
}

################################################################################

function ExitWithException($message) {
    if (strlen(trim($message)) > 3) {
        echo "<p><b>$message</b></p><br>";
        exit(1);
    } else { exit(1); }
}

################################################################################

function RemoveTrailingPuncMarks($string) {
    $string = trim($string);
    while (preg_match("/[[:punct:]]$/", $string) === 1) {
        $string = substr($string, 0, (strlen($string) - 1));
    }
    return $string;
}

################################################################################

function ExpandAbbreviatedBookNames($string_to_check) {
    $string_to_check = trim($string_to_check);

    # WARNING Longer abbreviations that do not contain spaces MUST appear before shorter
    # abbreviations in the array of abbreviations because word boundaries are used to match
    # abbreviated book names and the check loop breaks after finding the first match
    $abbrev_booknames = array("gen" => "Genesis",
                              "ge" => "Genesis",
                              "exod" => "Exodus",
                              "exo" => "Exodus",
                              "ex" => "Exodus",
                              "lev" => "Leviticus",
                              "le" => "Leviticus",
                              "lv" => "Leviticus",
                              "num" => "Numbers",
                              "deut" => "Deuteronomy",
                              "deu" => "Deuteronomy",
                              "de" => "Deuteronomy",
                              "josh" => "Joshua",
                              "jos" => "Joshua",
                              "judg" => "Judges",
                              "jdg" => "Judges",
                              "jgs" => "Judges",
                              "rut" => "Ruth",
                              "rth" => "Ruth",
                              "rt" => "Ruth",
                              "1sam" => "1 Samuel",
                              "2sam" => "2 Samuel",
                              "1 sam" => "1 Samuel",
                              "2 sam" => "2 Samuel",
                              "1kgs" => "1 Kings",
                              "2kgs" => "2 Kings",
                              "1 kgs" => "1 Kings",
                              "2 kgs" => "2 Kings",
                              "1chr" => "1 Chronicles",
                              "2chr" => "2 Chronicles",
                              "1 chr" => "1 Chronicles",
                              "2 chr" => "2 Chronicles",
                              "ezr" => "Ezra",
                              "neh" => "Nehemiah",
                              "esth" => "Esther",
                              "est" => "Esther",
                              "psalm" => "Psalms",
                              "psa" => "Psalms",
                              "pss" => "Psalms",
                              "ps" => "Psalms",
                              "prov" => "Proverbs",
                              "pro" => "Proverbs",
                              "qoheleth" => "Ecclesiastes",
                              "eccl" => "Ecclesiastes",
                              "ecc" => "Ecclesiastes",
                              "qoh" => "Ecclesiastes",
                              "song" => "Song of Solomon",
                              "sol" => "Song of Solomon",
                              "canticles" => "Song of Solomon",
                              "canticle" => "Song of Solomon",
                              "isa" => "Isaiah",
                              "jer" => "Jeremiah",
                              "je" => "Jeremiah",
                              "lam" => "Lamentations",
                              "la" => "Lamentations",
                              "eze" => "Ezekiel",
                              "dan" => "Daniel",
                              "da" => "Daniel",
                              "hos" => "Hosea",
                              "joe" => "Joel",
                              "amo" => "Amos",
                              "oba" => "Obadiah",
                              "jon" => "Jonah",
                              "mic" => "Micah",
                              "mi" => "Micah",
                              "nah" => "Nahum",
                              "na" => "Nahum",
                              "habbak" => "Habbakkuk",
                              "habak" => "Habbakkuk",
                              "habb" => "Habbakkuk",
                              "haba" => "Habbakkuk",
                              "hab" => "Habbakkuk",
                              "ha" => "Habbakkuk",
                              "zep" => "Zephaniah",
                              "ze" => "Zephaniah",
                              "hag" => "Haggai",
                              "zech" => "Zechariah",
                              "zec" => "Zechariah",
                              "mal" => "Malachi",
                              "matt" => "Matthew",
                              "mat" => "Matthew",
                              "mt" => "Matthew",
                              "mar" => "Mark",
                              "mk" => "Mark",
                              "luk" => "Luke",
                              "lu" => "Luke",
                              "lk" => "Luke",
                              "jhn" => "John",
                              "jno" => "John",
                              "jn" => "John",
                              "act" => "Acts",
                              "ac" => "Acts",
                              "rom" => "Romans",
                              "ro" => "Romans",
                              "1cor" => "1 Corinthians",
                              "2cor" => "2 Corinthians",
                              "1 cor" => "1 Corinthians",
                              "2 cor" => "2 Corinthians",
                              "gal" => "Galations",
                              "ga" => "Galations",
                              "eph" => "Ephesians",
                              "ep" => "Ephesians",
                              "phi" => "Philippians",
                              "col" => "Colossians",
                              "1ths" => "1 Thessalonians",
                              "2ths" => "2 Thessalonians",
                              "1 ths" => "1 Thessalonians",
                              "2 ths" => "2 Thessalonians",
                              "1th" => "1 Thessalonians",
                              "2th" => "2 Thessalonians",
                              "1 th" => "1 Thessalonians",
                              "2 th" => "2 Thessalonians",
                              "1thes" => "1 Thessalonians",
                              "2thes" => "2 Thessalonians",
                              "1 thes" => "1 Thessalonians",
                              "2 thes" => "2 Thessalonians",
                              "1thess" => "1 Thessalonians",
                              "2thess" => "2 Thessalonians",
                              "1 thess" => "1 Thessalonians",
                              "2 thess" => "2 Thessalonians",
                              "1tim" => "1 Timothy",
                              "2tim" => "2 Timothy",
                              "1 tim" => "1 Timothy",
                              "2 tim" => "2 Timothy",
                              "1ti" => "1 Timothy",
                              "2ti" => "2 Timothy",
                              "1 ti" => "1 Timothy",
                              "2 ti" => "2 Timothy",
                              "tit" => "Titus",
                              "phn" => "Philemon",
                              "heb" => "Hebrews",
                              "he" => "Hebrews",
                              "jms" => "James",
                              "jas" => "James",
                              "1pet" => "1 Peter",
                              "2pet" => "2 Peter",
                              "1 pet" => "1 Peter",
                              "2 pet" => "2 Peter",
                              "1Pe" => "1 Peter",
                              "2Pe" => "2 Peter",
                              "1 Pe" => "1 Peter",
                              "2 Pe" => "2 Peter",
                              "1jn" => "1 John",
                              "2jn" => "2 John",
                              "3jn" => "3 John",
                              "1 jn" => "1 John",
                              "2 jn" => "2 John",
                              "3 Jn" => "3 John",
                              "1jno" => "1 John",
                              "2jno" => "2 John",
                              "3jno" => "3 John",
                              "1 jno" => "1 John",
                              "2 jno" => "2 John",
                              "3 jno" => "3 John",
                              "jud" => "Jude",
                              "rev" => "Revelation",
                              "re" => "Revelation",);

    # Check if the search string begins with any of the supported abbreviated book names and replace it if found
    foreach ($abbrev_booknames as $key => $value) {
        #echo "checking \"$string_to_check\" for \"$key\"<br>";
        if (substr_compare($string_to_check, $key, 0, strlen($key), true) == 0) {
            #echo "found \"$key\" in \"$string_to_check\"<br>";
            #echo "Before: \"$string_to_check\"<br>";
            $string_to_check = preg_replace("/\b$key\b/i", $value, $string_to_check, 1);
            #echo "After: \"$string_to_check\"<br>";
            break;
        }
    }

    return $string_to_check;

    #WARNING, OLD do not use, only here for reference
    if (1 > 2) {
        if (preg_match_all("/\b[0-9]{1,3}:/", $string_to_check) >= 1) {
            # Get the book name and wrangle it to match the format of the keys of the abbreviated book names array
            $book = substr($string_to_check, 0, strpos($string_to_check, ":"));
            $book = substr($book, 0, strrpos($book, " "));
            $book = strtolower(trim($book));
            #echo "Checking array for: \"$book\"<br>";
            # If the abbreviated book name is found, replace it with the expanded version
            if (array_key_exists($book, $abbrev_booknames)) {
                # return the string given with the book name replaced
                $string_to_check = preg_replace("/\b$book\b/i", $abbrev_booknames[$book], $string_to_check, 1);
                #echo "After: \"$string_to_check\"<br>";
                return $string_to_check;
            } else {
                #echo "Key Not found: \"$book\"<br>";
            }
        }
        #echo "After: \"$string_to_check\"<br>";
        return $string_to_check;
    }
}

################################################################################

function IsABookName($term) {

    $booknames = array("Genesis",
                       "Exodus",
                       "Leviticus",
                       "Numbers",
                       "Deuteronomy",
                       "Joshua",
                       "Judges",
                       "Ruth",
                       "1 Samuel",
                       "2 Samuel",
                       "1 Kings",
                       "2 Kings",
                       "1 Chronicles",
                       "2 Chronicles",
                       "Ezra",
                       "Nehemiah",
                       "Esther",
                       "Psalms",
                       "Proverbs",
                       "Ecclesiastes",
                       "Song of Solomon",
                       "Isaiah",
                       "Jeremiah",
                       "Lamentations",
                       "Ezekiel",
                       "Daniel",
                       "Hosea",
                       "Joel",
                       "Amos",
                       "Obadiah",
                       "Jonah",
                       "Micah",
                       "Nahum",
                       "Habbakkuk",
                       "Zephaniah",
                       "Haggai",
                       "Zechariah",
                       "Malachi",
                       "Matthew",
                       "Mark",
                       "Luke",
                       "John",
                       "Acts",
                       "Romans",
                       "1 Corinthians",
                       "2 Corinthians",
                       "Galations",
                       "Galations",
                       "Ephesians",
                       "Philippians",
                       "Colossians",
                       "1 Thessalonians",
                       "2 Thessalonians",
                       "1 Timothy",
                       "2 Timothy",
                       "Titus",
                       "Philemon",
                       "Hebrews",
                       "James",
                       "1 Peter",
                       "2 Peter",
                       "1 John",
                       "2 John",
                       "3 John",
                       "Jude",
                       "Revelation");

    foreach ($booknames as $value) {
        if (strtolower($value) == strtolower($term)) {
            return true;
        }
    }
    return false;
}

################################################################################

?>
</form>
</div>
<a href="#" class="top">↑</a>
</body>
</html>
