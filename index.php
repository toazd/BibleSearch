<!DOCTYPE html>
<HTML lang="en">
    <HEAD>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Bible search tool</title>
        <STYLE type="text/css">

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
        </STYLE>
    </HEAD>
    <BODY>
        <DIV id="search_form">
        <SCRIPT>
            document.write("<H2><A HREF=\"" + window.location.href + "\" style=\"text-decoration: none\">King James Version (Cambridge)</A></H2>");
        </SCRIPT>
        <UL id="search_help">
            <LI>Search queries may be separated by semi-colons ";" (King of kings; John 3:16)</LI>
            <LI>Ranges may be specified with either a dash "-" (Psalm 150:2-5)<BR>
            or one or more commas "," (Psalm 150:1,3,5)</LI>
            <LI>Results will be in biblical order</LI>
        </UL>
        <FORM action="" method="post">
            <INPUT type="text" name="criteria" value="" size=50 maxlength=255 autofocus>
            <INPUT type="submit" name="submit" value="Find in Bible">
            <INPUT type="checkbox" id="casetoggle" name="casetoggle" value="FALSE">
            <LABEL for="casetoggle">Case-sensitive</LABEL>
            <BR><BR>
            <?php
                if (isset($_POST['submit'])) {

                    # Determine case sensitivity based on the checkbox status
                    if (isset($_POST['casetoggle'])) {
                        $case_toggle = true; # Yes
                    } else {
                        $case_toggle = false; # No/error/unknown/default
                    }

                    # Get the text to search for from the criteria input field
                    $search_for = $_POST['criteria'];

                    # If the search criteria contains only whitespace or control characters do nothing
                    if (trim($search_for) == "") { exit; }

                    # remove double quotes
                    if (strpos($search_for, "\"") !== false) {
                        $search_for = str_replace("\"", "", $search_for);
                    }

                    # remove single quotes
                    if (strpos($search_for, "\'") !== false) {
                        $search_for = str_replace("\'", "", $search_for);
                    }

                    # quickly test features
                    if ($search_for == "debug") {
                        echo "case_toggle: ", var_dump($case_toggle), "<BR>";
                        $search_for = "debug search string: used to run tests; my rock; James 1:10-5; sanctification; prince of peace; Mark 1:15,10,5; boaz; Joshua 1:1-1; John 3:16-17 3:17-16; Spirit of God; 3 john 1:7-11; king of kings; Genesis 1:1-10; and god saw; Cant 2:3,5,17,9; fowls; damsel; living; Ruth 2:; Romans 8:15-0; Obadiah 1:0-25; Joel 19; Nahum 3:3-9; Amos 3:3; Malachi 4:1-7; Jude 1:9-17";
                    }

                    # Bible text used to search.
                    # Each line follows the following format:
                    # Book <one_space> Book#:Verse# <one_space> <verse>
                    $bible_text = "KJV-Cambridge_UTF-8_notes_removed_ule.txt";

                    echo "Results for \"$search_for\"<BR><BR>";

                    # Support multiple searches per query, each seperated by a semi-colon ";"
                    # this foreach block will look for ranges of the format Book#:Verse#-Verse#
                    # and expand them (eg. Genesis 1:1-2 becomes Genesis 1:1; Genesis 1:2)
                    # Reverse order is also supported (eg. Genesis 1:2-1 becomes Genesis 1:1; Genesis 1:2)
                    foreach(explode(";", $search_for) as &$search) {

                        # support exactly one "dash" book#:verse#-verse# range or
                        # at least one "comma" book#:verse#,verse#,verse# separated list per group
                        if (preg_match_all("/[0-9]+:[0-9]+-[0-9]+/", $search) == 1 || preg_match_all("/[0-9]+:[0-9]+,[0-9]+/", $search) >= 1) {

                            # per group, ranges and commas together are not supported
                            if (strpos($search, "-") !== false && strpos($search, ",") !== false) {
                                echo "Ranges \"-\" and commas \",\" cannot be combined ($search)<BR>";
                                continue;
                            }

                            # process ranges specified using a dash "-" (eg. Genesis 1:1-12)
                            if (strpos($search, "-") !== false) {

                                # Get the book name and book number
                                $book = substr($search, 0, strpos($search, ":"));
                                $book_num = substr($book, strrpos($book, " "));
                                $book = substr($book, 0, strrpos($book, " "));

                                # Get the range by itself
                                $range = substr($search, strpos($search, ":"));
                                $range = substr($range, 1);
                                # Remove invalid characters (anything that isn't 0-9 or a dash "-")
                                $range = preg_replace("/[^0-9-]/", "", $range);
                                # Split at the dash "-"
                                $range = explode("-", $range); # range is now an array (it was a string)

                                # add the expanded range to the string of search criteria
                                # eg. Psalm 150:1-3 => Psalm 150:1; Psalm 150:2; Psalm 150:3
                                foreach (range($range[0], $range[1]) as $number) {
                                    # append the new entry to the string that will become the array of search criteria
                                    $search_for .= ";$book $book_num:$number";
                                }

                            # process "ranges" delimited by commas (eg. Genesis 1:7,1,12)
                            } elseif (strpos($search, ",") !== false) {

                                # Get the book name and book number
                                $book = substr($search, 0, strpos($search, ":"));
                                $book_num = substr($book, strrpos($book, " "));
                                $book = substr($book, 0, strrpos($book, " "));

                                # expand comma seperated verses and append them to the search array
                                # eg. James 1:5,6,22 => James 1:5, James 1:6, James 1:22
                                $range = explode(",", $search);
                                $pos = strpos($range[0], ":");
                                if ($pos !== false) {
                                    $range[0] = substr($range[0], ($pos + 1));
                                    foreach ($range as $search) {
                                        $search_for .= ";$book $book_num:$search";
                                    }
                                }
                            }
                        }
                    }

                    # turn the search criteria into an array, splitting at the semi-colons
                    $search_array = explode(";", $search_for);

                    unset($search_for);
                    foreach ($search_array as &$search_for) {
                        #echo "Before: \"$search_for\"<BR>";
                        $search_for = ExpandAbbreviatedBookName(trim($search_for));
                        #echo "After: \"$search_for\"<BR>";
                    }

                    unset($search_for, $search, $book, $book_num, $pos, $range, $handle, $match_count, $line); # just in case

                    # open the file of the text to search read only
                    $handle = @fopen($bible_text, "r");

                    # Counter for lines that match search criteria
                    $match_count = 0;

                    #$verse_min = 99999;

                    if ($handle) {

                        # get one line at a time from the file
                        while (($line = fgets($handle)) !== false) {

                            # if the line is empty or contains only control characters, skip it
                            if (trim($line == "")) { continue; } # just in case

                            # Get book name and book number
                            # Step 1 Return a portion of $search starting from the beginning and ending at the first colon encountered from the beginning
                            $book = substr($line, 0, strpos($line, ":"));
                            # Return a portion of $book beginning at the first space found starting from the end and ending at the first colon from the beginning
                            $book_num = substr($book, strrpos($book, " ")); # From here we can more easily grab the book number
                            # Step 2 Return a portion of $book starting at the beginning and ending at the first space encountered from the end
                            $book = substr($book, 0, strrpos($book, " "));

                            /*
                            # WIP feature
                            # Get the verse text by itself
                            $verse = substr($line, strpos($line, ":"));
                            $verse = substr($verse, strpos($verse, " "));
                            # remove brackets (Cambridge version uses them to show where original italics were)
                            $verse = preg_replace("/[\[\]]/", "", $verse);
                            $verse_len = strlen(trim($verse));

                            if ($verse_len < $verse_min) {
                                $verse_min = $verse_len;
                                $verse_min_text = $line;
                            }
                            */

                            # iterate search_array one element at a time
                            foreach($search_array as $search_for) {

                                # If its too short, dont do anything
                                if (strlen(trim($search_for)) <= 1) {
                                    echo "Search criteria \"$search_for\" too short<BR>";
                                    break 2;
                                }

                                # remove all leading spaces
                                while (substr($search_for, 0, 1) == " ") {
                                    $search_for = substr($search_for, 1);
                                }

                                # replace double spaces with spaces, might not be needed anymore
                                if (strpos($search_for, "  ") !== false) {
                                    $search_for = str_replace("  ", " ", $search_for);
                                }

                                # Exact verse number matches
                                # this is a good candidate for a toggle/option/checkbox for the user
                                # append a space to the end if the search string ends with a number preceded by a colon (eg. :4)
                                # otherwise, Genesis 1:1 will match Genesis 1:1 and also Genesis 1:13, Genesis 1:17, etc.
                                # by adding the space Genesis 1:1 will only match against Genesis 1:1
                                if (preg_match("/:[0-9]+$/", $search_for)) {
                                    $search_for .= " ";
                                }

                                # Case in-sensitive
                                if ($case_toggle === false) {

                                    # if line contains search_for, case in-sensitive
                                    if (stripos($line, $search_for) !== false) {

                                        # add a line break at the end. with this there
                                        # is no need wrap the output in <pre> tags
                                        $new_line = str_replace("\n", "\n<BR>", $line);

                                        # Show italics instead of the original brackets
                                        $new_line = str_replace("[", "<I>", $new_line);
                                        $new_line = str_replace("]", "</I>", $new_line);

                                        # Highlight search terms
                                        # preserve the original text since we are in case-insensitive mode
                                        $search_for_orig = substr($new_line, stripos($new_line, $search_for), strlen($search_for));
                                        $new_line = substr_replace($new_line, "<SPAN style=\"background-color:lightgreen\">$search_for_orig</SPAN>", stripos($new_line, $search_for_orig), strlen($search_for_orig));

                                        # Bold book names - replace only first occurance with bold
                                        if (strpos($new_line, $book) !== false) {
                                            $new_line = preg_replace("/$book/", "<B>$book</B>", $new_line, 1);
                                        }

                                        # word and character counts
                                        #echo "(W", (count(explode(" ", $verse)) - 1), ")(C$verse_len)", "\t$new_line";

                                        # Output the result to the page
                                        echo "$new_line";

                                        # Increment the match counter by 1
                                        $match_count++;

                                        # avoid outputting duplicate array matches by skipping to checking the next line instead of the rest of the search array
                                        continue 2;
                                    }
                                } elseif ($case_toggle === true) {

                                    # Case sensitive

                                    # if line contains search_for, case sensitive
                                    if (strpos($line, $search_for) !== false) {

                                        # Change formatting to be more suitable for html output
                                        # Outside of <pre> tags \n (the invisible control character new line) are not treated the same in html
                                        $new_line = str_replace("\n", "\n<BR>", $line);

                                        # Show italics instead of the original brackets
                                        $new_line = str_replace( "[", "<I>", $new_line);
                                        $new_line = str_replace( "]", "</I>", $new_line);

                                        # Highlight search terms
                                        $new_line = str_replace($search_for, "<SPAN style=\"background-color:lightgreen\">$search_for</SPAN>", $new_line);

                                        # Bold book names - replace first occurance from the beginning with bold
                                        # preg_replace searches left to right, so we limit it to one replacement
                                        if (strpos($new_line, $book) !== false) {
                                            $new_line = preg_replace("/$book/", "<B>$book</B>", $new_line, 1);
                                        }

                                        # Output the result to the page
                                        echo "$new_line";

                                        # Increment the match counter by 1
                                        $match_count++;

                                        # avoid outputting duplicate array matches by skipping to checking the next line instead of the rest of the search array
                                        continue 2;
                                    }
                                }
                            }
                        }
                    } else {
                        echo "File open error! ($bible_text)<BR>";
                    }

                    # Display a summary of the results based on the number of matches
                    # Zero
                    if ($match_count === 0) {
                        echo "<P>No matches were found</P><BR>";
                    }
                    # Exactly 1
                    if ($match_count === 1) {
                        echo "<P>Found $match_count matching line</P><BR>";
                    }
                    # More than 1
                    if ($match_count > 1) {
                        echo "<P>Found $match_count matching lines</P><BR>";
                        #echo "Shortest: $verse_min characters<BR>", "$verse_min_text";
                    }

                    # Close the file that was opened for searching
                    fclose($handle);
                }

                function ExpandAbbreviatedBookName($string_to_check) {
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
                                              "jgs" => "Judges",
                                              "judg" => "Judges",
                                              "judge" => "Judges",
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

                    if (preg_match_all("/\ [0-9]+:/", $string_to_check) >= 1) {
                        # Get the book name and wrangle it to match the format of the keys of the abbreviated book names array
                        $book = substr($string_to_check, 0, strpos($string_to_check, ":"));
                        $book = substr($book, 0, strrpos($book, " "));
                        $book = strtolower(trim($book));
                        # If the abbreviated book name is found, replace it with the expanded version
                        if (array_key_exists($book, $abbrev_booknames)) {
                            # return the string given with the book name replaced
                            return preg_replace("/$book/i", $abbrev_booknames[$book], $string_to_check, 1);
                        }
                    }
                    return $string_to_check;
                }
            ?>
        </FORM>
        </DIV>
    </BODY>
</HTML>
