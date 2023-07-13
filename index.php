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

* {
    font-family: "Code New Roman";
}

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

.tooltip:hover .tooltiptext {
    visibility: visible;
}

body {
    background-color: #f0e8bd /* #d9cf98 */;
    color: #000000;
    font-size: 14px;
}

p {
    font-size: 16px;
}

span {
    font-size: 16px;
}

#search_help {
    font-size: 12px;
}

/*
#search_form {
    margin-left:   10px;
    margin-right:  10px;
    margin-top:    10px;
    margin-bottom: 10px;
    display:       block;
    outline-style: hidden;
}
*/
#search_form {
    margin-left:   15%;
    margin-right:  15%;
    margin-top:    5%;
    margin-bottom: 5%;
    display:       block;
    outline-style: solid;
    outline-width: 1px;
    outline-offset: 20px;
    outline-color: black;
    border-radius: 20px;
}

input {
    font-size: 16px;
    padding: 5px;
    align: center;
}

.submit_button {
    border-radius: 15px;
    border: none;
    /*box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2), 0 6px 20px 0 rgba(0,0,0,0.19);*/
}

.submit_button:hover {
    box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
}

.search_criteria {

}

</style>
</head>
<body>
<div id="search_form">

<!--
<script>
document.write("<H2><A HREF=\"" + window.location.href + "\" style=\"text-decoration: none\">King James Version (Cambridge)</A></H2>");
</script>
-->

<!--
<ul id="search_help">
<li>Ranges may be specified with either a dash "-" (Psalm 150:2-5)<br>
or one or more commas "," (Psalm 150:1,3,5)</li>
<li>Out of order matches allows the search terms to appear in any order</li>
<li>Fuzzy matches attempts to match lines but allows missing and mispelled words</li>
<li>Results will be in biblical order</li>
</ul>
-->

<form action="" method="post" name="search_form" enctype="text/plain accept-charset="UTF-8">

<input type="text" name="criteria" class="search_criteria" value="" size=50 maxlength=255 autofocus>
<input type="submit" name="submit_button" class="submit_button" value="Search">

<br><br>

<?php

# TESTING only
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
##############

/*
 *    if (SpellCheck("thine")) {
 *    echo "true<br>";
 * } else {
 * echo "false<br>";
 * }
 * exit;
 */

if (isset($_POST['submit_button'])) {

    $start_time = microtime(true);

    # Get the text to search for from the criteria input field
    $search_for = $_POST['criteria'];

    # remove punctuation that are also regex modifiers
    $search_for = preg_replace("/[\(\)\'\"\[\]]/", "", $search_for);

    # if the search criteria is too short, display an error
    if (strlen(trim($search_for)) <= 3) {
        ExitWithException("Search criteria \"$search_for\" too short");
    }

    # Bible text used to search.
    # Each line follows the following format:
    # Book <one_space> Book#:Verse# <one_space> <verse>
    $bible_text = "KJV-Cambridge_UTF-8_notes_removed_ule.txt";

    # expand book name abbreviations
    $search_for = ExpandAbbreviatedBookNames($search_for);

    ####
    $exact_match_count = 0;
    $exact_match_results = "";
    ExactMatchCaseInsensitive($exact_match_count, $exact_match_results);

    if ($exact_match_count > 0) {
        # exactly 1
        if ($exact_match_count == 1) {
            echo "<p>Found $exact_match_count exact match for \"$search_for\"</p><br>";
        }
        # More than 1
        if ($exact_match_count > 1) {
            echo "<p>Found $exact_match_count exact matches for \"$search_for\"</p><br>";
        }
    } else {
        echo "<p>No exact matches found for \"$search_for\"</p><br>";
    }

    ####
    $near_match_count = 0;
    $near_match_results = "";
    NearMatchCaseInsensitive($near_match_count, $near_match_results);

    if ($near_match_count > 0) {
        # exactly 1
        if ($near_match_count == 1) {
            echo "<p>Found $near_match_count near match for \"$search_for\"</p><br>";
        }
        # More than 1
        if ($near_match_count > 1) {
            echo "<p>Found $near_match_count near matches for \"$search_for\"</p><br>";
        }
    } else {
        echo "<p>No near matches found for \"$search_for\"</p><br>";
    }

    echo "<p><u>Exact matches for \"$search_for\"</u></p><br>";


    # open the file of the text to search read only
    $handle = @fopen($bible_text, "r");

    # near matches require 3 or more words
    if ((count(explode(" ", $search_for))) >= 3) {
        echo "<p><u>Near matches</u></p><br>";
        if ($handle) {

            # get one line at a time from the file
            while (($line = fgets($handle)) !== false) {

                $replacement_count = 0;

                # Remove brackets originally used to indicate italics
                $new_line = str_replace("[", "", $line);
                $new_line = str_replace("]", "", $new_line);

                # if there is no exact match
                if (preg_match("/\b$search_for\b/i", $new_line)) {
                    # do nothing
                } else {
                    $new_line = CheckForConsecutiveSubstrings($search_for, $new_line, $replacement_count);

                    if ($replacement_count > 0) {
                        $new_line = BoldBookName($new_line);
                        echo "$new_line<br><br>";
                        #$near_match_text = $near_match_text . "$new_line<br>";
                        $near_match_count += 1;
                    }
                }
            }
        }


        #if ($exact_match_count == 0) {

        # if at least one near match was found, display the result
        if ($near_match_count > 0) {
            # Display a summary of the results based on the number of matches
            # Exactly 1
            if ($near_match_count == 1) {
                echo "<p>Found $near_match_count near match</p><br>";
            }
            # More than 1
            if ($near_match_count > 1) {
                echo "<p>Found $near_match_count near matches</p><br>";
            }
        } else {
            echo "No near matches found<br><br>";
        }

    }

    fclose($handle);
    $end_time = microtime(true);
    echo "Finished in ", round($end_time - $start_time, 6), " seconds<br>";
}

################################################################################

ExactMatchCaseInsensitive(&$exact_match_count, &$exact_match_results) {
    # open the file of the text to search read only
    $handle = @fopen($bible_text, "r");

    if ($handle) {

        # get one line at a time from the file
        while (($line = fgets($handle)) !== false) {
            # TESTING ONLY
            #if (!preg_match("/Ezekiel 16:/i", $line)) { continue; }

            # Remove brackets originally used to indicate italics
            $line = str_replace("[", "", $line);
            $line = str_replace("]", "", $line);

            # if line contains the pattern
            if (preg_match("/\b$search_for\b/i", $line)) {

                # replace newline with a break
                # no need for <pre> tags
                $new_line = str_replace("\n", "<br>", $line);

                $new_line = HighlightNoTooltip($new_line, $search_for);

                $new_line = BoldBookName($new_line);

                # Output the result to the page
                echo "$new_line<br>";

                # Increment the match counter by 1
                $exact_match_count += 1;
            }
        }
    }

    # Close the file that was opened for searching to reset the file position pointer
    fclose($handle);
}

################################################################################

NearMatchCaseInsensitive(&$near_match_count, &$near_match_results) {

}

################################################################################

function CheckForConsecutiveSubstrings($needle, $haystack, &$replacement_count) {
    $search_array = explode(" ", $needle);
    $search_word_count = count($search_array);
    $new_line = $haystack;

    # 3 words or more only
    if ($search_word_count >= 3) {
        # subtract one word from the end at a time until the minimum of 2 words is reached and check if it is in haystack
        for($i = ($search_word_count - 1); $i >= 2; $i--) {
            $check_for = implode(" ", array_slice($search_array, 0, $i));
            #echo "$check_for<br><br>";
            if (preg_match("/\b$check_for\b/i", $new_line)) {
                $new_line = HighlightNoTooltip($new_line, $check_for);
                $replacement_count += 1;
                #return $new_line;
            }
        }

        # subtract one word from the beginning at a time until the minimum of 2 words is reached and check if it is in haystack
        for($i = 1; $i < ($search_word_count - 1) ; $i++) {
            $check_for = implode(" ", array_slice($search_array, $i, $search_word_count));
            #echo "$check_for<br><br>";
            if (preg_match("/\b$check_for\b/i", $new_line)) {
                $new_line = HighlightNoTooltip($new_line, $check_for);
                $replacement_count += 1;
                #return $new_line;
            }
        }
    }
    return $new_line;
}

################################################################################

function HighlightNoTooltip($line, $term) {

    # get the position where the string to be highlighted begins
    $term_beg = stripos($line, $term);

    # get the position where the highlighting should end
    $term_end = $term_beg + strlen($term) + strlen("<span style=\"background-color:lightgreen\">");

    $line = substr_replace($line, "<span style=\"background-color:lightgreen\">", $term_beg, 0);
    $line = substr_replace($line, "</span>", $term_end, 0);

    return $line;
}

################################################################################

function BoldBookName($line) {
    $space_after_colon_pos = strpos($line, " ", strpos($line, ":"));

    $book = substr($line, 0, $space_after_colon_pos);
    # Replace only the first occurance
    return preg_replace("/\b$book\b/", "<b>$book</b>", $line, 1);
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
        echo "$message<br>";
        exit(1);
    } else { exit(1); }
}

################################################################################

function RemoveTrailingPuncMarks($string) {
    $string = trim($string);
    while (preg_match("/[[:punct:]]$/", $string)) {
        $string = substr($string, 0, (strlen($string) - 1));
    }
    return $string;
}

################################################################################

function ExpandAbbreviatedBookNames($string_to_check) {
    $string_to_check = trim($string_to_check);
    #echo "Before: \"$string_to_check\"<br>";
    # Support abbreviated book names and variations
    # eg. Jas = James, Hab = Habakkuk, Cant = Song of Solomon
    $abbrev_booknames = array("ge" => "Genesis",
                              "gen" => "Genesis",
                              "ex" => "Exodus",
                              "exo" => "Exodus",
                              "exod" => "Exodus",
                              "le" => "Leviticus",
                              "lev" => "Leviticus",
                              "lv" => "Leviticus",
                              "num" => "Numbers",
                              "deu" => "Deuteronomy",
                              "de" => "Deuteronomy",
                              "deut" => "Deuteronomy",
                              "jos" => "Joshua",
                              "josh" => "Joshua",
                              "jdg" => "Judges",
                              "jgs" => "Judges",
                              "judg" => "Judges",
                              "judge" => "Judges",
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
                              "est" => "Esther",
                              "esth" => "Esther",
                              "ps" => "Psalms",
                              "psa" => "Psalms",
                              "pss" => "Psalms",
                              "psalm" => "Psalms",
                              "pro" => "Proverbs",
                              "prov" => "Proverbs",
                              "ecc" => "Ecclesiastes",
                              "eccl" => "Ecclesiastes",
                              "qoh" => "Ecclesiastes",
                              "sol" => "Song of Solomon",
                              "song" => "Song of Solomon",
                              "can" => "Song of Solomon",
                              "cant" => "Song of Solomon",
                              "isa" => "Isaiah",
                              "is" => "Isaiah",
                              "je" => "Jeremiah",
                              "jer" => "Jeremiah",
                              "la" => "Lamentations",
                              "lam" => "Lamentations",
                              "eze" => "Ezekiel",
                              "da" => "Daniel",
                              "dan" => "Daniel",
                              "hos" => "Hosea",
                              "joe" => "Joel",
                              "amo" => "Amos",
                              "oba" => "Obadiah",
                              "jon" => "Jonah",
                              "mi" => "Micah",
                              "mic" => "Micah",
                              "na" => "Nahum",
                              "nah" => "Nahum",
                              "ha" => "Habbakkuk",
                              "hab" => "Habbakkuk",
                              "ze" => "Zephaniah",
                              "zep" => "Zephaniah",
                              "hag" => "Haggai",
                              "zec" => "Zechariah",
                              "zech" => "Zechariah",
                              "mal" => "Malachi",
                              "mat" => "Matthew",
                              "mt" => "Matthew",
                              "matt" => "Matthew",
                              "mar" => "Mark",
                              "mk" => "Mark",
                              "lu" => "Luke",
                              "luk" => "Luke",
                              "lk" => "Luke",
                              "jhn" => "John",
                              "jn" => "John",
                              "jno" => "John",
                              "act" => "Acts",
                              "ac" => "Acts",
                              "ro" => "Romans",
                              "rom" => "Romans",
                              "1cor" => "1 Corinthians",
                              "2cor" => "2 Corinthians",
                              "1 cor" => "1 Corinthians",
                              "2 cor" => "2 Corinthians",
                              "ga" => "Galations",
                              "gal" => "Galations",
                              "ep" => "Ephesians",
                              "eph" => "Ephesians",
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
                              "re" => "Revelation",
                              "rev" => "Revelation");

    # NOTE fix this for search terms that lack a colon such as "rev 2"
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

################################################################################
?>
</form>
</div>
</body>
</html>
