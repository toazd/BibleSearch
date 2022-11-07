<HTML lang="en">
    <HEAD>
        <meta charset="UTF-8">
        <STYLE type="text/css">
        @font-face {
            font-family: "Code New Roman";
            src: url("Code New Roman.woff") format("truetype");
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

        #search_form {
            margin-left:   1%;
            margin-right:  1%;
            margin-top:    1%;
            margin-bottom: 1%;
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

        <FORM action="" method="post">
            <INPUT type="text" name="criteria" value="" size=50 maxlength=255 autofocus>
            <INPUT type="submit" name="submit" value="Find in Bible">
            <INPUT type="checkbox" id="casetoggle" name="casetoggle" value="FALSE">
            <LABEL for="casetoggle">Case-sensitive</LABEL>
            <BR><BR>
            <?php
                #ini_set("auto_detect_line_endings", true);

                if (isset($_POST['submit'])) {

                    # If case sensitive checkbox is checked
                    if (isset($_POST['casetoggle'])) {
                        $case_toggle = true;
                    } else {
                        $case_toggle = false;
                    }

                    # Get the text to search for from the criteria input field
                    $search_for = $_POST['criteria'];

                    # If the search criteria is empty do nothing
                    if ($search_for == "" || trim($search_for) == "") { exit; }

                    # remove double quotes
                    if (strpos($search_for, "\"") !== false) { $search_for = str_replace("\"", "", $search_for); }

                    # remove single quotes
                    if (strpos($search_for, "\'") !== false) { $search_for = str_replace("\'", "", $search_for); }

                    # quickly test features
                    if ($search_for == "debug") {
                        echo "case_toggle: ";
                        var_dump($case_toggle);
                        echo "<br>";

                        $search_for = "debug string: used to run tests; my rock; James 1:10-5; sanctification; prince of peace; booz; boaz; Joshua 1:1-1; John 3:16-17 3:17-16; Spirit of God; 3 john 1:7-11; king of kings; Genesis 1:1-10; and god saw; Song of Solomon 2:1-2; fowls; Song of Solomon 2:3-4; damsel; living; Ruth 2:; \"; \'; Romans 8:15-0; Obadiah 1:0-25; Joel 19; Nahum 3:3-9; Amos 3:3; Malachi 4:1-7; Jude 1:9-17";
                    }

                    # Won't display correctly if the font isn't fixed-width
                    if ($search_for == "kitty") {
                    # v2
                    echo "<pre>
                                                                      ██
                                                  ██                ██  ██
                                                ██  ██            ██░░  ██
                                                ██░░░░▓▓▓▓▓▓▓▓▓▓▓▓░░░░░░██
                                                ██░░▓▓░░░░  ░░▒▒▓▓░░░░░░██
                                                ████  ░░  ░░  ░░░░░░░░░░██
                                              ▓▓░░░░░░░░  ░░░░░░░░░░░░░░░░▓▓
                                              ██░░░░░░░░░░░░░░░░░░░░░░░░░░██
                                              ██  ▒▒░░░░░░  ▒▒▒▒░░░░░░░░░░██
                                              ██  ██▒▒░░░░▓▓▒▒░░░░        ██
                                            ██  ░░  ░░    ░░░░░░    ░░░░░░██
                                            ██░░    ██      ░░░░░░      ░░██
                                            ██░░░░██  ████░░░░░░░░░░░░░░░░██
                                              ██░░░░      ░░░░░░░░░░░░░░████
                                              ██░░░░    ░░░░░░░░░░░░░░░░████
                                                ██░░░░░░░░░░░░░░░░░░░░▓▓██
                                                  ██░░░░░░░░░░░░░░░░░░░░  ██
                                                ██░░░░░░░░░░░░░░░░░░░░  ░░  ██
                                                ██░░░░░░░░░░░░░░░░░░  ░░░░    ██
                                              ██░░░░  ░░░░░░░░░░░░░░░░░░  ░░░░  ██
                                            ██░░░░░░  ░░░░░░░░░░░░░░░░░░  ░░░░    ██
                                          ██░░░░░░    ░░░░░░░░░░░░░░░░░░░░░░      ░░▓▓
                                        ██░░░░░░░░    ░░░░░░░░░░░░░░░░░░░░  ░░░░░░░░  ██
                                      ██  ░░░░░░░░    ░░░░░░░░░░░░░░░░░░░░░░░░░░    ░░██
                                    ▓▓░░░░░░░░  ▓▓    ░░░░░░░░░░░░░░░░░░░░░░░░░░  ░░░░  ▓▓
                                  ▓▓░░░░░░    ▓▓██  ░░░░░░░░░░░░░░░░░░░░░░░░░░░░  ░░    ░░▓▓
                                ██        ████    ██░░░░░░░░░░░░░░░░░░  ████  ░░░░░░  ░░░░██
                                ██        ▓▓██    ██░░░░░░░░░░░░░░░░░░░░████░░░░░░░░  ░░░░██
                                ██    ▓▓▓▓        ██░░░░░░░░░░░░░░░░▓▓▓▓    ░░░░░░░░░░░░░░░░▓▓
                                ██  ██            ██░░░░░░░░░░░░░░██    ░░░░░░░░░░░░░░      ██
                                  ██▓▓          ██░░░░░░  ██░░░░░░██  ░░░░░░░░░░░░░░░░  ░░░░██
                                  ██▓▓          ██░░░░░░  ██░░░░░░██  ░░░░░░░░░░░░░░░░  ░░░░██
                          ▓▓▓▓▓▓▓▓░░░░          ██░░░░░░  ██░░░░▓▓░░  ░░░░░░░░░░░░░░░░  ░░░░██
                        ▓▓██▒▒██▒▒▓▓          ▓▓░░░░░░  ▓▓░░  ░░██  ░░░░░░░░░░░░░░░░░░░░░░░░██
                        ██▒▒██▒▒████        ██░░░░░░  ██    ▓▓████████░░░░░░░░░░░░░░░░░░░░░░██
                        ██▒▒██▓▓▒▒██      ▓▓    ░░  ▓▓██  ▓▓░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░██
                        ████▒▒██▒▒██    ██  ██    ██    ██    ██                        ██░░░░██
            ▓▓██████▓▓██  ██████▓▓      ██████▓▓▓▓      ██▓▓██████▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓████░░░░██
          ▓▓░░░░░░░░░░░░  ░░░░  ░░            ░░        ░░  ░░▒▒                    ░░██░░    ██
            ██▓▓▓▓████████████████████                ████████              ██████████  ░░░░████
                                      ██              ██    ██▓▓▓▓▓▓████▓▓██░░  ░░░░░░  ░░░░██
                    ██████████████████                ██      ░░    ░░    ░░░░░░  ░░░░░░  ██
                  ██                                    ██  ░░░░  ░░░░░░  ░░░░░░  ░░██████
                    ▓▓▓▓▓▓▓▓▓▓                            ▓▓▓▓██▓▓▓▓░░░░  ░░▓▓▓▓▓▓▓▓
                    ░░░░  ░░░░                                  ▒▒████████▓▓░░
</pre>";
                        exit;

                        # v1
                        echo "<pre>
                        _
                        \`*-.
                         )  _`-.
                        .  : `. .
                        : _   '  \
                        ; *` _.   `*-._
                        `-.-'          `-.
                          ;       `       `.
                          :.       .        \
                          . \  .   :   .-'   .
                          '  `+.;  ;  '      :
                          :  '  |    ;       ;-.
                          ; '   : :`-:     _.`* ;
                       .*' /  .*' ; .*`- +'  `*'
                       `*-*   `*-*  `*-*'
                        </pre>";
                        exit;
                    }

                    # Bible text used to search
                    # each line follows the following format:
                    # Book <one_space> Book#:Verse# <one_space> <verse>
                    $bible_text = "KJV-Cambridge_UTF-8_notes_removed_ule.txt";

                    echo "Results for \"$search_for\"<BR><BR>";

                    # Support multiple searches per query, each seperated by a semi-colon ";"
                    # this foreach block will look for ranges of the format Book#:Verse#-Verse#
                    # and expand them (eg. Genesis 1:1-2 becomes Genesis 1:1; Genesis 1:2)
                    foreach(explode(";", $search_for) as &$search) {

                        # only support one book:verse#-verse# range per group
                        # support one or more comma separated verses
                        if (preg_match_all("/[0-9]+:[0-9]+-[0-9]+/", $search) == 1 || preg_match_all("/[0-9]+:[0-9]+,[0-9]+/", $search) >= 1) {

                            # per group, ranges and commas together are not supported
                            if (strpos($search, "-") !== false && strpos($search, ",")) {
                                echo "Warning: Only one range \"-\" or comma \",\" per group is supported ($search)<BR>";
                                continue;
                            }

                            if (strpos($search, "-") !==false) {
                                # expand ranges
                                $range = substr($search, strpos($search, ":"));
                                $range = substr($range, 1);

                                # Remove invalid characters (anything that isn't 0-9 or a dash "-")
                                $range = preg_replace("/[^0-9-]/", "", $range);

                                # Split at the dash "-"
                                $range = explode("-", $range); # range is now an array

                                # add the expanded range to the string of search criteria
                                # eg. Psalm 150:1-3 => Psalm 150:1; Psalm 150:2; Psalm 150:3
                                foreach (range($range[0], $range[1]) as $number) {
                                    $book = substr($search, 0, strpos($search, ":"));
                                    $book_num = substr($book, strrpos($book, " "), strpos($search, ":"));
                                    $book = substr($book, 0, strrpos($book, " "));
                                    $search_for .= ";$book $book_num:$number";
                                }
                            } elseif (strpos($search, ",") !== false) {
                                # Remove invalid characters (anything that isn't 0-9 or a comma ",")
                                $search = preg_replace("/[^0-9,]/", "", $search);

                                # expand comma seperated verses
                                $range = explode(",", $search);
                                if (strpos($range[0], ":") !==false) {
                                    $range[0] = substr($range[0], (strpos($range[0], ":") + 1));
                                }
                                var_dump($range);
                                echo "<BR>";
                            }
                        }
                    }

                    # turn the search criteria into an array, splitting at the semi-colons
                    $search_array = explode(";", $search_for);
                    unset($search_for); # just in case

                    # open the file of the text to search read only
                    $handle = @fopen($bible_text, "r");

                    # Counter for lines that match search criteria
                    $match_count = 0;

                    $verse_min = 99999;

                    if ($handle) {

                        # get one line at a time from the file
                        while (($line = fgets($handle)) !== false) {

                            # if the line is empty or contains only a newline, skip it
                            if ($line == "" || $line == "\n") { continue; } # just in case

                            # Get the verse text by itself
                            $verse = substr($line, strpos($line, ":"));
                            $verse = substr($verse, strpos($verse, " "));
                            # remove brackets
                            $verse = preg_replace("/[\[\]]/", "", $verse);
                            $verse_len = strlen(trim($verse));

                            if ($verse_len < $verse_min) {
                                $verse_min = $verse_len;
                                $verse_min_text = $line;
                            }

                            # iterate search_array one element at a time
                            foreach($search_array as $search_for) {

                                # If its too short, dont do anything
                                if (strlen($search_for) <= 1 || $search_for == "  ") { /*echo "Search criteria \"$search_for\" too short!<BR>";*/ continue; }

                                # remove all leading spaces
                                while (substr($search_for, 0, 1) == " ") { $search_for = substr($search_for, 1); }

                                # replace double spaces with spaces
                                if (strpos($search_for, "  ") !== false) { $search_for = str_replace("  ", " ", $search_for); }

                                # append a space to the end if the search string ends with a number
                                # otherwise, Genesis 1:1 would also match Genesis 1:13, Genesis 1:20, etc.
                                if (preg_match("/:[0-9]+$/", $search_for)) { $search_for .= " "; }

                                # Case in-sensitive
                                if ($case_toggle === false) {

                                    # if line contains search_for, case in-sensitive
                                    if (stripos($line, $search_for) !== false) {

                                        # Change formatting to be more suitable for html output
                                        $new_line = str_replace("\n", "\n<BR>", $line);

                                        # Show italics instead of the original brackets
                                        $new_line = str_replace("[", "<I>", $new_line);
                                        $new_line = str_replace("]", "</I>", $new_line);

                                        # Bold book names - get book name
                                        $book = substr($new_line, 0, strpos($new_line, ":"));
                                        $book = substr($book, 0, strrpos($book, " "));

                                        # Highlight search terms
                                        $search_for_orig = substr($new_line, stripos($new_line, $search_for), strlen($search_for));
                                        $new_line = substr_replace($new_line, "<SPAN style=\"background-color:lightgreen\">$search_for_orig</SPAN>", stripos($new_line, $search_for_orig), strlen($search_for_orig));

                                        # Bold book names - replace only first occurance with bold
                                        if (strpos($new_line, $book) !== false) { $new_line = preg_replace("/$book/", "<B>$book</B>", $new_line, 1); }

                                        #echo "(W";
                                        #echo (count(explode(" ", $verse)) - 1);
                                        #echo ")(C$verse_len)";
                                        #echo "\t$new_line";

                                        echo "$new_line";

                                        # Increment the match counter by 1
                                        $match_count++;

                                        # avoid outputting duplicate array matches
                                        continue 2;
                                    }
                                } elseif ($case_toggle === true) {

                                    # Case sensitive

                                    # if line contains search_for, case sensitive
                                    if (strpos($line, $search_for) !== false) {

                                        # Change formatting to be more suitable for html output
                                        $new_line = str_replace("\n", "\n<BR>", $line);

                                        # Bold book names - get book name
                                        $book = substr($new_line, 0, strpos($new_line, ":"));
                                        $book = substr($book, 0, strrpos($book, " "));

                                        # Show italics instead of the original brackets
                                        $new_line = str_replace( "[", "<I>", $new_line);
                                        $new_line = str_replace( "]", "</I>", $new_line);

                                        # Highlight search terms
                                        $new_line = str_replace($search_for, "<SPAN style=\"background-color:lightgreen\">$search_for</SPAN>", $new_line);

                                        # Bold book names - replace first occurance with bold
                                        if (strpos($new_line, $book) !== false) { $new_line = preg_replace("/$book/", "<B>$book</B>", $new_line, 1); }

                                        echo "$new_line";

                                        # Increment the match counter by 1
                                        $match_count++;

                                        # avoid outputting duplicate array matches
                                        continue 2;
                                    }
                                }
                            }
                        }
                    } else {
                        echo "File open error! (file: $bible_text)<BR>";
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
                        #echo "Shortest: $verse_min characters<BR>";
                        #echo "$verse_min_text";
                    }

                    # Close the text file
                    fclose($handle);
                }
            ?>
        </FORM>
        </DIV>
    </BODY>
</HTML>
