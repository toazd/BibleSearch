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

            <input type="text" name="criteria" value="" size=50 maxlength=255 autofocus>
            <input type="submit" name="submit" value="Search">

            <br><br>
            <?php
            # Have php output all errors to the page to ease testing
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);

#echo similar_text("teh", "the");
#exit;

                if (isset($_POST['submit'])) {

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

                    echo "<u>Exact matches for \"$search_for\"</u><br><br>";

                    # expand book name abbreviations
                    $search_for = ExpandAbbreviatedBookNames($search_for);

                    # open the file of the text to search read only
                    $handle = @fopen($bible_text, "r");

                    # Counter for lines that match search criteria
                    $match_count = 0;

                    if ($handle) {

                        # get one line at a time from the file
                        while (($line = fgets($handle)) !== false) {
# TESTING ONLY
#if (!preg_match("/Ezekiel 16:/i", $line)) { continue; }
                            # if the line is empty or contains only control characters, skip it
                            #if (trim($line) == "") { continue; } # just in case

                            # Remove brackets originally used to indicate italics
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

                            ############################
                            # perform a "normal" search and if no results are found then attmept to find fuzzy matches
                            # a normal search is whole words only, case-insensitive, in-order
                            ############################

                            # if the last character is a punctuation mark, don't use a trailing word boundary for matching
                            # to avoid checking for a double word boundary for search criteria such as "night;"
                            #if (preg_match("/[[:punct:]]$/", $search_for)) {
                            #    $pattern = "/\b$search_for/i";
                            #} else {
                            #    $pattern = "/\b$search_for\b/i";
                            #}
                            $pattern = "/\b$search_for\b/i";
                            # if line contains the pattern
                            if (preg_match($pattern, $line)) {

                                # add a line break at the end. using this there
                                # is no need wrap the output in <pre> tags
                                $new_line = str_replace("\n", "<br>", $line);

                                # Highlight search terms
                                # preserve the original text since we are in case-insensitive mode
                                $search_for_orig = substr($new_line, stripos($new_line, $search_for), strlen($search_for));
                                $new_line = preg_replace($pattern, "<span style=\"background-color:lightgreen\">$search_for_orig</span>", $new_line);

                                # Bold book names - replace only the first occurance with bold
                                $new_line = preg_replace("/\b$book\b/", "<b>$book</b>", $new_line, 1);

                                # Output the result to the page
                                echo "$new_line<br>";

                                # Increment the match counter by 1
                                $match_count++;

                            }
                        }
                    }

                    # Close the file that was opened for searching
                    fclose($handle);

                    # if at least one exact match was found, display the result
                    if ($match_count > 0) {
                        # Display a summary of the results based on the number of matches
                        # Exactly 1
                        if ($match_count == 1) {
                            echo "<p>Found $match_count exact match</p><br>";
                        }
                        # More than 1
                        if ($match_count > 1) {
                            echo "<p>Found $match_count exact matches</p><br>";
                        }
                    # if no exact matches were found, attempt fuzzy matches
                    } elseif ($match_count == 0) {

                        echo "No exact matches found<br><br>";

                        # open the file of the text to search read only
                        $handle = @fopen($bible_text, "r");

                        #echo "<u>Near matches for \"$search_for\"</u><br><br>";
                        echo "<u>Near matches</u><br><br>";

                        if ($handle) {

                            $match_rank_array = [];

                            # get one line at a time from the file
                            while (($line = fgets($handle)) !== false) {
# TESTING ONLY
#if (!preg_match("/Ezekiel 16:23/i", $line)) { continue; }

                                # Remove brackets originally used to indicate italics
                                $line = str_replace("[", "", $line);
                                $line = str_replace("]", "", $line);

                                # Fuzzy matching testing
                                $search_array = explode(" ", $search_for);
                                $search_word_count = count($search_array);
                                $search_for_words_in_line_count = 0;
                                $line_word_count = count(explode(" ", $line));
                                $new_line = $line;
                                $exact_match_count = 0;
                                $fuzzy_match_count = 0;
                                $check_words = "";

                                # FUZZY MATCH - IN-ORDER/PARTIAL IN-ORDER

                                # FUZZY MATCH - OUT-OF-ORDER
                                # check every search word against every verse word and try to catch mispellings and close matches
                                foreach($search_array as $search_for_word) {
                                    foreach (explode(" ", $new_line) as $line_word) {
                                        #remove punctuation
                                        $line_word = preg_replace("/[[:punct:]]/", "", $line_word);
                                        $line_word = trim($line_word);

                                        $pattern = "/\b" . preg_replace("/[[:punct:]]/", "", $line_word) . "\b/i";
                                        # exact word match
                                        if (preg_match("/\b$search_for_word\b/i", $line_word)) {
                                            #echo "Exact word match: \"$search_for_word\" to \"$line_word\"<br>";
                                            #echo "pattern: $pattern<br>";
                                            $new_line = preg_replace($pattern, "<span style=\"background-color:lightgreen\">$line_word</span>", $new_line);
                                            $exact_match_count += 1;
                                            $search_for_words_in_line_count += 1;
                                            $check_words = $check_words . " " . $line_word;
                                            continue 2;
                                        }

                                        # WARNING this can't be here
                                        # don't fuzzy match 3 or less words, too many matches
                                        #if ($search_word_count <= 3) { continue; }

                                        # fuzzy word match
                                        # don't match to words that are too significantly different
                                        if (strlen($line_word) < (1.3 * strlen($search_for_word))) {
                                            # if the words are similar
                                            if (similar_text(strtolower($search_for_word), strtolower($line_word)) >= (0.66 * strlen($search_for_word))) {
                                                #echo "Near match: \"$search_for_word\" to \"$line_word\"<br>";
                                                $new_line = preg_replace($pattern, "<div class=\"tooltip\" style=\"background-color:lightgreen\">$line_word<span class=\"tooltiptext\">$search_for_word</span></div>", $new_line);
                                                $fuzzy_match_count += 1;
                                                $search_for_words_in_line_count += 1;
                                                $check_words = $check_words . " " . $line_word;
                                                continue 2;
                                            }
                                        }
                                    }
                                }

                                $total_matches = $exact_match_count + $fuzzy_match_count;
                                $threshold = round(0.66 * $search_word_count, 0, PHP_ROUND_HALF_DOWN);
                                if ($total_matches >= $threshold) {
                                    #if (stripos($line, trim($check_words)) !== false) {
                                        # bold book names
                                        $book = substr($line, 0, strpos($line, ":"));
                                        $book = substr($book, 0, strrpos($book, " "));
                                        $new_line = preg_replace("/\b$book\b/", "<b>$book</b>", $new_line, 1);

                                        #echo "(", trim($check_words), ") $new_line<br>";
                                        #echo "($total_matches/$threshold) $new_line<br>";
                                        # Add to ranking array
                                        $rank_element = $total_matches . " " . $new_line;
                                        $match_rank_array[] = $rank_element;
                                        $match_count += 1;
                                    #}
                                } else {
                                    #echo "matches: ", $exact_match_count + $fuzzy_match_count, "<br>";
                                    #echo "thresh: ", round(0.33 * $search_word_count, 0, PHP_ROUND_HALF_DOWN), "<br>";
                                }
                            }
                        }

                        # print the results in decending order (closet match first)
                        if (count($match_rank_array) > 1) {
                            rsort($match_rank_array);
                            foreach($match_rank_array as $match_element) {
                                echo substr($match_element, strpos($match_element, " ")), "<br><br>";
                                #echo "$match_element<br><br>";
                            }
                        }

                        fclose($handle);

                        # if at least one fuzzy match was found, display the result
                        if ($match_count > 0) {
                            # Display a summary of the results based on the number of matches
                            # Exactly 1
                            if ($match_count == 1) {
                                echo "<p>Found $match_count near match</p><br>";
                            }
                            # More than 1
                            if ($match_count > 1) {
                                echo "<p>Found $match_count near matches</p><br>";
                            }
                        } else {
                            echo "No near matches found<br><br>";
                        }
                    }
                    $end_time = microtime(true);

                    echo "Finished in ", round($end_time - $start_time, 3), " seconds<br>";
                }

                function ExitWithException($message) {
                    if (strlen(trim($message)) > 3) {
                        echo "$message<br>";
                        exit;
                    } else { exit; }
                }

                function RemoveTrailingPuncMarks($string) {
                    $string = trim($string);
                    while (preg_match("/[[:punct:]]$/", $string)) {
                        $string = substr($string, 0, (strlen($string) - 1));
                    }
                    return $string;
                }

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
            ?>
            </form>
        </div>
    </body>
</html>
