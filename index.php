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

        body {
            background-color: #f0e8bd;
            color: #000000;
            font-size: 16px;
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

        #search_form {
            margin-left:   10px;
            margin-right:  10px;
            margin-top:    10px;
            margin-bottom: 10px;
            display:       block;
            outline-style: hidden;
        }
        </style>
    </head>
    <body>
        <div id="search_form">
        <script>
            document.write("<H2><A HREF=\"" + window.location.href + "\" style=\"text-decoration: none\">King James Version (Cambridge)</A></H2>");
        </script>
        <ul id="search_help">
            <li>Ranges may be specified with either a dash "-" (Psalm 150:2-5)<br>
            or one or more commas "," (Psalm 150:1,3,5)</li>
            <li>Out of order matches allows the search terms to appear in any order</li>
            <li>Fuzzy matches attempts to match lines but allows missing and mispelled words</li>
            <li>Results will be in biblical order</li>
        </ul>
        <form action="" method="post" name="search_form" enctype="text/plain accept-charset="UTF-8">

            <input type="text" name="criteria" value="" size=50 maxlength=255 autofocus>
            <input type="submit" name="submit" value="Find in Bible">
            <br><br>
            <input type="checkbox" id="casetoggle" name="casetoggle" value="false">
            <label for="casetoggle">Case-sensitive</label>

            <input type="checkbox" id="aiooo" name="aiooo" value="false">
            <label for="aiooo">Show out-of-order matches</label>

            <input type="checkbox" id="fuzzy" name="fuzzy" value="false" checked>
            <label for="fuzzy">Fuzzy matching</label>

            <br><br>
            <?php
                if (isset($_POST['submit'])) {

                    # Determine case sensitivity based on the checkbox status
                    if (isset($_POST['casetoggle'])) {
                        $case_toggle = true; # Yes
                    } else {
                        $case_toggle = false; # No/error/unknown/default
                    }

                    # WIP all inclusive, out-of-order mode
                    if (isset($_POST['aiooo'])) {
                        $all_inclusive_toggle = true; # Yes
                    } else {
                        $all_inclusive_toggle = false; # No/error/unknown/default
                    }

                    # Fuzzy results
                    if (isset($_POST['fuzzy'])) {
                        $fuzzy_results_toggle = true; # Yes
                    } else {
                        $fuzzy_results_toggle = false; # No/error/unknown/default
                    }

                    # NOTE this is a reminder to implement this feature
                    if ($case_toggle && $fuzzy_results_toggle) {
                        echo "Fuzzy results currently does not support case-sensitive mode<br>";
                        exit;
                    }

                    # Get the text to search for from the criteria input field
                    $search_for = $_POST['criteria'];

                    # remove double quotes
                    if (strpos($search_for, "\"") !== false) {
                        $search_for = str_replace("\"", "", $search_for);
                    }

                    # remove single quotes
                    if (strpos($search_for, "\'") !== false) {
                        $search_for = str_replace("\'", "", $search_for);
                    }

                    # If the search criteria contains only whitespace and/or control characters, exit
                    if (trim($search_for) == "") { exit; }

                    # Bible text used to search.
                    # Each line follows the following format:
                    # Book <one_space> Book#:Verse# <one_space> <verse>
                    $bible_text = "KJV-Cambridge_UTF-8_notes_removed_ule.txt";

                    echo "Results for \"$search_for\"<br><br>";

                    # expand book name abbreviations
                    $search_for = ExpandAbbreviatedBookNames($search_for);

                    # open the file of the text to search read only
                    $handle = @fopen($bible_text, "r");

                    # Counter for lines that match search criteria
                    $match_count = 0;

                    if ($handle) {

                        # get one line at a time from the file
                        while (($line = fgets($handle)) !== false) {

                            # if the line is empty or contains only control characters, skip it
                            if (trim($line) == "") { continue; } # just in case

                            # Remove brackets, for now
                            # they cause a lot of trouble with phrases
                            $line = str_replace("[", "", $line);
                            $line = str_replace("]", "", $line);

                            # Get book name and book number
                            # Step 1 Return a portion of $search starting from the beginning and ending at the first colon encountered from the beginning
                            $book = substr($line, 0, strpos($line, ":"));
                            # Return a portion of $book beginning at the first space found starting from the end and ending at the first colon from the beginning
                            $book_num = substr($book, strrpos($book, " ")); # From here we can more easily grab the book number
                            # Step 2 Return a portion of $book starting at the beginning and ending at the first space encountered from the end
                            $book = substr($book, 0, strrpos($book, " "));
                            $book = trim($book);
                            $book_num = trim($book_num);

                            # All-inclusive, out-of-order mode means that two criterion must be met:
                            # 1. all search words (delimited by spaces) must be present
                            # 2. the words can appear in any order
                            if ($all_inclusive_toggle === false) {

                                # If its too short, dont do anything
                                if (strlen(trim($search_for)) <= 1) {
                                    echo "Search criteria \"$search_for\" too short<br>";
                                    break;
                                }

                                # Case in-sensitive
                                if ($case_toggle === false) {

                                    # Fuzzy matching testing
                                    if ($fuzzy_results_toggle === true) {
                                        $search_array = explode(" ", $search_for);
                                        $search_word_count = count($search_array);
                                        $search_for_words_in_line_count = 0;

                                        $line_word_count = explode(" ", $line);

                                        foreach($search_array as $search_for_word) {
                                            if (preg_match("/\b$search_for_word\b/", $line)) {
                                                $search_for_words_in_line_count += 1;
                                            }
                                        }

                                        $search_words_in_line_ratio = ($search_for_words_in_line_count / $search_word_count);
                                        if ($search_words_in_line_ratio > 0.50) {
                                        #if ($search_for_words_in_line_count >= 6) {
                                            $new_line = preg_replace("/\b$book\b/", "<b>$book</b>", $line, 1);

                                            echo "($search_words_in_line_ratio) $new_line<br>";
                                            $match_count += 1;
                                        }
                                    } elseif ($fuzzy_results_toggle === false) {
                                        # if line contains search_for, case in-sensitive
                                        if (preg_match("/\b$search_for\b/i", $line)) {

                                            # add a line break at the end. using this there
                                            # is no need wrap the output in <pre> tags
                                            $new_line = str_replace("\n", "\n<br>", $line);

                                            # Highlight search terms
                                            # preserve the original text since we are in case-insensitive mode
                                            $search_for_orig = substr($new_line, stripos($new_line, $search_for), strlen($search_for));
                                            $new_line = preg_replace("/\b$search_for\b/i", "<span style=\"background-color:lightgreen\">$search_for_orig</span>", $new_line);

                                            # Bold book names - replace only the first occurance with bold
                                            #if (strpos($new_line, $book) !== false) {
                                            $new_line = preg_replace("/\b$book\b/", "<b>$book</b>", $new_line, 1);
                                            #}

                                            # Output the result to the page
                                            echo "$new_line";

                                            # Increment the match counter by 1
                                            $match_count++;

                                            # avoid outputting duplicate array matches by skipping to checking the next line instead of the rest of the search array
                                            continue;
                                        }
                                    }
                                } elseif ($case_toggle === true) {

                                    # Case sensitive

                                    # if line contains search_for, case sensitive
                                    if (preg_match("/\b$search_for\b/", $line)) {

                                        # Change formatting to be more suitable for html output
                                        $new_line = str_replace("\n", "\n<br>", $line);

                                        # Highlight search terms
                                        $new_line = preg_replace("/\b$search_for\b/", "<span style=\"background-color:lightgreen\">$search_for</span>", $new_line);

                                        # Bold book names - replace first occurance from the beginning with bold
                                        # preg_replace searches left to right, so we limit it to one replacement
                                        #if (strpos($new_line, $book) !== false) {
                                        $new_line = preg_replace("/\b$book\b/", "<b>$book</b>", $new_line, 1);
                                        #}

                                        # Output the result to the page
                                        echo "$new_line";

                                        # Increment the match counter by 1
                                        $match_count++;

                                        # avoid outputting duplicate array matches by skipping to checking the next line instead of the rest of the search array
                                        continue;
                                    }
                                }
                            } elseif ($all_inclusive_toggle === true) {
                                # check if the line contains every search word
                                # if it doesn't, skip to checking the next line from the file
                                # if it does, continue onward to process the line and output it as a match
                                foreach (explode(" ", $search_for) as $search_for_word) {
                                    if ($search_for_word == "") { continue; }

                                    # determine case-sensitive status
                                    if ($case_toggle === true) {
                                        $pattern = "/\b$search_for_word\b/";
                                    } elseif ($case_toggle === false) {
                                        $pattern = "/\b$search_for_word\b/i";
                                    }

                                    if (preg_match($pattern, $line) == 0) {
                                        # if any one word is not found in the line this isn't a match
                                        # so skip to the next line from the file
                                        continue 2;
                                    }
                                }

                                # bold book names
                                # Get book name and book number
                                $book = substr($line, 0, strpos($line, ":"));
                                #$book_num = substr($book, strrpos($book, " "));
                                $book = substr($book, 0, strrpos($book, " "));
                                $book = trim($book);
                                #$book_num = trim($book_num);

                                # make a persistent copy to work with, otherwise
                                # only the last word in the search criteria is highlighted
                                $new_line = $line;
                                # highlight search words
                                foreach (explode(" ", $search_for) as $search_for_word) {
                                    if ($search_for_word == "") { continue; }
                                    if ($case_toggle === true) {
                                        $new_line = preg_replace("/\b$search_for_word\b/", "<span style=\"background-color:lightgreen\">$search_for_word</span>", $new_line);
                                    } elseif ($case_toggle === false) {
                                        # preserve the original text so it doesn't get replaced with the correct text but the wrong case pattern
                                        $search_for_orig = substr($new_line, stripos($new_line, $search_for_word), strlen($search_for_word));
                                        $new_line = preg_replace("/\b$search_for_word\b/i", "<span style=\"background-color:lightgreen\">$search_for_orig</span>", $new_line);
                                    }
                                }

                                # Bold book names - replace only first occurance with bold
                                #if (strpos($new_line, $book) !== false) {
                                $new_line = preg_replace("/\b$book\b/", "<b>$book</b>", $new_line, 1);
                                #}

                                # print the line as a match
                                echo "$new_line<br>";

                                # Increment the match counter by 1
                                $match_count++;
                            }
                        }
                    } else {
                        echo "File open error! ($bible_text)<br>";
                    }

                    # Display a summary of the results based on the number of matches
                    # Zero
                    if ($match_count === 0) {
                        echo "<p>No matches were found</p><br>";
                    }
                    # Exactly 1
                    if ($match_count === 1) {
                        echo "<p>Found $match_count matching line</p><br>";
                    }
                    # More than 1
                    if ($match_count > 1) {
                        echo "<p>Found $match_count matching lines</p><br>";
                        #echo "Shortest: $verse_min characters<br>", "$verse_min_text";
                    }

                    # Close the file that was opened for searching
                    fclose($handle);
                }

                function ExpandAbbreviatedBookNames($string_to_check) {
                    $string_to_check = trim($string_to_check);
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
                                              "ha" => "Habakkuk",
                                              "hab" => "Habakkuk",
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

                    if (preg_match_all("/\b[0-9]{1,3}:/", $string_to_check) >= 1) {
                        # Get the book name and wrangle it to match the format of the keys of the abbreviated book names array
                        $book = substr($string_to_check, 0, strpos($string_to_check, ":"));
                        $book = substr($book, 0, strrpos($book, " "));
                        $book = strtolower(trim($book));
                        # If the abbreviated book name is found, replace it with the expanded version
                        if (array_key_exists($book, $abbrev_booknames)) {
                            # return the string given with the book name replaced
                            return preg_replace("/\b$book\b/i", $abbrev_booknames[$book], $string_to_check, 1);
                        }
                    }
                    return $string_to_check;
                }
            ?>
            </form>
        </div>
    </body>
</html>
