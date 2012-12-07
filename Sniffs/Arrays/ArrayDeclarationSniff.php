<?php
class ezcs_Sniffs_Arrays_ArrayDeclarationSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_ARRAY);

    }


    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The current file being checked.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Array keyword should be lower case.
        if (strtolower($tokens[$stackPtr]['content']) !== $tokens[$stackPtr]['content']) {
            $phpcsFile->addError('Array keyword should be lower case; expected "array" but found "%s"', $stackPtr, 'NotLowerCase', array($tokens[$stackPtr]['content']));
        }

        $arrayStart   = $tokens[$stackPtr]['parenthesis_opener'];
        $arrayEnd     = $tokens[$arrayStart]['parenthesis_closer'];
        $keywordStart = $tokens[$stackPtr]['column'];

        if ($arrayStart != ($stackPtr + 1)) {
            $phpcsFile->addError('There must be no space between the "array" keyword and the opening parenthesis', $stackPtr, 'SpaceAfterKeyword');
        }

        // Check for empty arrays.
        $content = $phpcsFile->findNext(array(T_WHITESPACE), ($arrayStart + 1), ($arrayEnd + 1), true);
        if ($content === $arrayEnd) {
            // Empty array, but if the brackets aren't together, there's a problem.
            if (($arrayEnd - $arrayStart) !== 1) {
                $phpcsFile->addError('Empty array declaration must have no space between the parentheses', $stackPtr, 'SpaceInEmptyArray');

                // We can return here because there is nothing else to check. All code
                // below can assume that the array is not empty.
                return;
            }
        }

        if ($tokens[$arrayStart]['line'] === $tokens[$arrayEnd]['line']) {
            // Single line array.
            // Check if there are multiple values. If so, then it has to be multiple lines
            // unless it is contained inside a function call or condition.
            $nextComma  = $arrayStart;
            $valueCount = 0;
            $commas     = array();
            while (($nextComma = $phpcsFile->findNext(array(T_COMMA), ($nextComma + 1), $arrayEnd)) !== false) {
                $valueCount++;
                $commas[] = $nextComma;
            }

            // Now check each of the double arrows (if any).
            $nextArrow = $arrayStart;
            while (($nextArrow = $phpcsFile->findNext(T_DOUBLE_ARROW, ($nextArrow + 1), $arrayEnd)) !== false) {
                if ($tokens[($nextArrow - 1)]['code'] !== T_WHITESPACE) {
                    $content = $tokens[($nextArrow - 1)]['content'];
                    $data    = array($content);
                    $phpcsFile->addError('Expected 1 space between "%s" and double arrow; 0 found', $nextArrow, 'NoSpaceBeforeDoubleArrow', $data);
                } else {
                    $spaceLength = strlen($tokens[($nextArrow - 1)]['content']);
                    if ($spaceLength !== 1) {
                        $content = $tokens[($nextArrow - 2)]['content'];
                        $data    = array(
                                    $content,
                                    $spaceLength,
                                   );
                        $phpcsFile->addError('Expected 1 space between "%s" and double arrow; %s found', $nextArrow, 'SpaceBeforeDoubleArrow', $data);
                    }
                }

                if ($tokens[($nextArrow + 1)]['code'] !== T_WHITESPACE) {
                    $content = $tokens[($nextArrow + 1)]['content'];
                    $data    = array($content);
                    $phpcsFile->addError('Expected 1 space between double arrow and "%s"; 0 found', $nextArrow, 'NoSpaceAfterDoubleArrow', $data);
                } else {
                    $spaceLength = strlen($tokens[($nextArrow + 1)]['content']);
                    if ($spaceLength !== 1) {
                        $content = $tokens[($nextArrow + 2)]['content'];
                        $data    = array(
                                    $content,
                                    $spaceLength,
                                   );
                        $phpcsFile->addError('Expected 1 space between double arrow and "%s"; %s found', $nextArrow, 'SpaceAfterDoubleArrow', $data);
                    }
                }
            }//end while

            if ($valueCount > 0) {
                // We have a multiple value array that is inside a condition or
                // function. Check its spacing is correct.
                foreach ($commas as $comma) {
                    if ($tokens[($comma + 1)]['code'] !== T_WHITESPACE) {
                        $content = $tokens[($comma + 1)]['content'];
                        $data    = array($content);
                        $phpcsFile->addError('Expected 1 space between comma and "%s"; 0 found', $comma, 'NoSpaceAfterComma', $data);
                    } else {
                        $spaceLength = strlen($tokens[($comma + 1)]['content']);
                        if ($spaceLength !== 1) {
                            $content = $tokens[($comma + 2)]['content'];
                            $data    = array(
                                        $content,
                                        $spaceLength,
                                       );
                            $phpcsFile->addError('Expected 1 space between comma and "%s"; %s found', $comma, 'SpaceAfterComma', $data);
                        }
                    }

                    if ($tokens[($comma - 1)]['code'] === T_WHITESPACE) {
                        $content     = $tokens[($comma - 2)]['content'];
                        $spaceLength = strlen($tokens[($comma - 1)]['content']);
                        $data        = array(
                                        $content,
                                        $spaceLength,
                                       );
                        $phpcsFile->addError('Expected 0 spaces between "%s" and comma; %s found', $comma, 'SpaceBeforeComma', $data);
                    }
                }//end foreach
            }//end if

            return;
        }//end if

        // Check the closing bracket is on a new line.
        $lastContent = $phpcsFile->findPrevious(array(T_WHITESPACE), ($arrayEnd - 1), $arrayStart, true);
        if ($tokens[$lastContent]['line'] !== ($tokens[$arrayEnd]['line'] - 1)) {
            $phpcsFile->addError('Closing parenthesis of array declaration must be on a new line', $arrayEnd, 'CloseBraceNewLine');
        }

        $nextToken  = $stackPtr;
        $lastComma  = $stackPtr;
        $keyUsed    = false;
        $singleUsed = false;
        $lastToken  = '';
        $indices    = array();
        $maxLength  = 0;

        // Find all the double arrows that reside in this scope.
        while (($nextToken = $phpcsFile->findNext(array(T_DOUBLE_ARROW, T_COMMA, T_ARRAY), ($nextToken + 1), $arrayEnd)) !== false) {
            $currentEntry = array();

            if ($tokens[$nextToken]['code'] === T_ARRAY) {
                // Let subsequent calls of this test handle nested arrays.
                $indices[] = array(
                              'value' => $nextToken,
                             );
                $nextToken = $tokens[$tokens[$nextToken]['parenthesis_opener']]['parenthesis_closer'];
                continue;
            }

            if ($tokens[$nextToken]['code'] === T_COMMA) {
                $stackPtrCount = 0;
                if (isset($tokens[$stackPtr]['nested_parenthesis']) === true) {
                    $stackPtrCount = count($tokens[$stackPtr]['nested_parenthesis']);
                }

                if (count($tokens[$nextToken]['nested_parenthesis']) > ($stackPtrCount + 1)) {
                    // This comma is inside more parenthesis than the ARRAY keyword,
                    // then there it is actually a comma used to seperate arguments
                    // in a function call.
                    continue;
                }

                if ($keyUsed === true && $lastToken === T_COMMA) {
                    $phpcsFile->addError('No key specified for array entry; first entry specifies key', $nextToken, 'NoKeySpecified');
                    return;
                }

                if ($keyUsed === false) {
                    if ($tokens[($nextToken - 1)]['code'] === T_WHITESPACE) {
                        $content     = $tokens[($nextToken - 2)]['content'];
                        $spaceLength = strlen($tokens[($nextToken - 1)]['content']);
                        $data        = array(
                                        $content,
                                        $spaceLength,
                                       );
                        $phpcsFile->addError('Expected 0 spaces between "%s" and comma; %s found', $nextToken, 'SpaceBeforeComma', $data);
                    }

                    // Find the value, which will be the first token on the line,
                    // excluding the leading whitespace.
                    $valueContent = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($nextToken - 1), null, true);
                    while ($tokens[$valueContent]['line'] === $tokens[$nextToken]['line']) {
                        if ($valueContent === $arrayStart) {
                            // Value must have been on the same line as the array
                            // parenthesis, so we have reached the start of the value.
                            break;
                        }

                        $valueContent--;
                    }

                    $valueContent = $phpcsFile->findNext(T_WHITESPACE, ($valueContent + 1), $nextToken, true);
                    $indices[]    = array('value' => $valueContent);
                    $singleUsed   = true;
                }//end if

                $lastToken = T_COMMA;
                continue;
            }//end if

            if ($tokens[$nextToken]['code'] === T_DOUBLE_ARROW) {
                if ($singleUsed === true) {
                    $phpcsFile->addWarning('Key specified for array entry; first entry has no key', $nextToken, 'KeySpecified');
                    return;
                }

                $currentEntry['arrow'] = $nextToken;
                $keyUsed               = true;

                // Find the start of index that uses this double arrow.
                $indexEnd   = $phpcsFile->findPrevious(T_WHITESPACE, ($nextToken - 1), $arrayStart, true);
                $indexStart = $phpcsFile->findPrevious(T_WHITESPACE, $indexEnd, $arrayStart);

                if ($indexStart === false) {
                    $index = $indexEnd;
                } else {
                    $index = ($indexStart + 1);
                }

                $currentEntry['index']         = $index;
                $currentEntry['index_content'] = $phpcsFile->getTokensAsString($index, ($indexEnd - $index + 1));

                $indexLength = strlen($currentEntry['index_content']);
                if ($maxLength < $indexLength) {
                    $maxLength = $indexLength;
                }

                // Find the value of this index.
                $nextContent           = $phpcsFile->findNext(array(T_WHITESPACE), ($nextToken + 1), $arrayEnd, true);
                $currentEntry['value'] = $nextContent;
                $indices[]             = $currentEntry;
                $lastToken             = T_DOUBLE_ARROW;
            }//end if
        }//end while

        /*
            This section checks for arrays that don't specify keys.

            Arrays such as:
               array(
                'aaa',
                'bbb',
                'd',
               );
        */


        /*
            Below the actual indentation of the array is checked.
            Errors will be thrown when a key is not aligned, when
            a double arrow is not aligned, and when a value is not
            aligned correctly.
            If an error is found in one of the above areas, then errors
            are not reported for the rest of the line to avoid reporting
            spaces and columns incorrectly. Often fixing the first
            problem will fix the other 2 anyway.

            For example:

            $a = array(
                  'index'  => '2',
                 );

            In this array, the double arrow is indented too far, but this
            will also cause an error in the value's alignment. If the arrow were
            to be moved back one space however, then both errors would be fixed.
        */

        $numValues = count($indices);

        $indicesStart = ($keywordStart + 1);
        $arrowStart   = ($indicesStart + $maxLength + 1);
        $valueStart   = ($arrowStart + 3);
        foreach ($indices as $index) {
            if (isset($index['index']) === false) {
                // Array value only.
                if (($tokens[$index['value']]['line'] === $tokens[$stackPtr]['line']) && ($numValues > 1)) {
                    $phpcsFile->addError('The first value in a multi-value array must be on a new line', $stackPtr, 'FirstValueNoNewline');
                }

                continue;
            }

            if (($tokens[$index['index']]['line'] === $tokens[$stackPtr]['line'])) {
                $phpcsFile->addError('The first index in a multi-value array must be on a new line', $stackPtr, 'FirstIndexNoNewline');
                continue;
            }

            // Check each line ends in a comma.
            if ($tokens[$index['value']]['code'] !== T_ARRAY) {
                $nextComma = $phpcsFile->findNext(array(T_COMMA), ($index['value'] + 1));

                // Check that there is no space before the comma.
                if ($nextComma !== false && $tokens[($nextComma - 1)]['code'] === T_WHITESPACE) {
                    $content     = $tokens[($nextComma - 2)]['content'];
                    $spaceLength = strlen($tokens[($nextComma - 1)]['content']);
                    $data        = array(
                                    $content,
                                    $spaceLength,
                                   );
                    $phpcsFile->addError('Expected 0 spaces between "%s" and comma; %s found', $nextComma, 'SpaceBeforeComma', $data);
                }
            }
        }//end foreach

    }//end process()


}//end class

?>
