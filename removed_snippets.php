<?php

# Fuzzy matching testing
$search_array = explode(" ", $search_for);
$search_word_count = count($search_array);
$line_word_count = count(explode(" ", $line));
$new_line = $line;
$exact_match_count = 0;
$fuzzy_match_count = 0;
$ranking = 0;

# check every search word against every verse word and try to catch mispellings and close matches
# exact word matches a get a basic highlight and fuzzy matches get a highlight with a tooltip that
# displays which word mapped to the highlighted term
foreach($search_array as $search_for_word) {
	foreach (explode(" ", $new_line) as $line_word) {
		#remove punctuation
		$line_word = preg_replace("/[[:punct:]]/", "", trim($line_word));

		$pattern = "/\b" . preg_replace("/[[:punct:]]/", "", $line_word) . "\b/i";
		# exact word match
		if (preg_match("/\b$search_for_word\b/i", $line_word)) {
			#echo "Exact word match: \"$search_for_word\" to \"$line_word\"<br>";
			#echo "pattern: $pattern<br>";
			$new_line = preg_replace($pattern, "<span style=\"background-color:lightgreen\">$line_word</span>", $new_line, -1, $replacement_count);
			$match_array[] = $new_line;
			$exact_match_count += $replacement_count;
			continue 2;
		}

		# fuzzy word match
		# don't match to words that are too significantly different
		if (strlen($line_word) < (1.3 * strlen($search_for_word))) {
			# if the words are similar
			if (similar_text(strtolower($search_for_word), strtolower($line_word)) >= (0.66 * strlen($search_for_word))) {
				#echo "Near match: \"$search_for_word\" to \"$line_word\"<br>";
				$new_line = preg_replace($pattern, "<div class=\"tooltip\" style=\"background-color:lightgreen\">$line_word<span class=\"tooltiptext\">$search_for_word</span></div>", $new_line, -1, $replacement_count);
				$match_array[] = $new_line;
				#$fuzzy_match_count += $replacement_count;
				continue 2;
			}
		}
	}
}
###################################################################################

    # print the results in decending order (closet match first)
    if (count($match_rank_array) > 1) {
        arsort($match_rank_array, SORT_NATURAL);
        foreach($match_rank_array as $match_element) {
            #echo substr($match_element, strpos($match_element, " ")), "<br><br>";
            echo "$match_element<br><br>";
        }
    }
###################################################################################
function CheckConsecutiveSubstringsOld($needle, $haystack, &$replacement_count) {
    # check if haystack contains any consecutive substrings from needle
    # NOTE min=2 words, max= (searchwordcount -1) words
    #
    # starting from the END of the search string subtract one word at a time
    # and check if the resulting substring is in haystack
    $search_array = explode(" ", $needle);
    $search_word_count = count($search_array);
    $search_upper_limit = ($search_word_count - 1);
    $new_line = $haystack;
    $count_end = 0;
    $count_beg = 0;

    $substr_end = trim(substr($needle, 0, strrpos($needle, " ")));
    $substr_beg = trim(substr($needle, strpos($needle, " ")));
    #$word_count_end = count(explode(" ", $substr_end));
    $word_count = count(explode(" ", $substr_beg));
    while ($word_count >= 2 && $word_count <= $search_upper_limit) {

        if (preg_match("/\b$substr_beg\b/i", $haystack)) {
            $new_line = preg_replace("/\b$substr_beg\b/i", HighlightNoTooltip($new_line, $substr_beg), $new_line, -1, $count_beg);
        }

        if (preg_match("/\b$substr_end\b/i", $haystack)) {
            $new_line = preg_replace("/\b$substr_end\b/i", HighlightNoTooltip($new_line, $substr_end), $new_line, -1, $count_end);
        }

        # subtract one word from the beginning
        $substr_beg = trim(substr($substr_beg, strpos($substr_beg, " ")));

        # subtract one word from the end
        $substr_end = trim(substr($substr_end, 0, strrpos($substr_end, " ")));
        # update word count
        #$word_count_end = count(explode(" ", $substr_end));
        # update word count
        $word_count = count(explode(" ", $substr_beg));
    }

    $replacement_count = ($count_end + $count_beg);

    return $new_line;
}

