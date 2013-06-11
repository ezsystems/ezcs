<?php
class ezcs_Sniffs_Functions_FunctionCallSignatureSniff implements PHP_CodeSniffer_Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_STRING, T_ARRAY);

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Find the next non-empty token.
        $openBracket = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr + 1), null, true);

        if ($tokens[$openBracket]['code'] !== T_OPEN_PARENTHESIS) {
            // Not a function call.
            return;
        }

        if (isset($tokens[$openBracket]['parenthesis_closer']) === false) {
            // Not a function call.
            return;
        }

        // Find the previous non-empty token.
        $search   = PHP_CodeSniffer_Tokens::$emptyTokens;
        $search[] = T_BITWISE_AND;
        $previous = $phpcsFile->findPrevious($search, ($stackPtr - 1), null, true);
        if ($tokens[$previous]['code'] === T_FUNCTION) {
            // It's a function definition, not a function call.
            return;
        }

        $closeBracket = $tokens[$openBracket]['parenthesis_closer'];

        if (($stackPtr + 1) !== $openBracket) {
            // Checking this: $value = my_function[*](...).
            $error = 'Space before opening parenthesis of function call prohibited';
            $phpcsFile->addError($error, $stackPtr, 'SpaceBeforeOpenBracket');
        }

        $next = $phpcsFile->findNext(T_WHITESPACE, ($closeBracket + 1), null, true);
        if ($tokens[$next]['code'] === T_SEMICOLON) {
            if (in_array($tokens[($closeBracket + 1)]['code'], PHP_CodeSniffer_Tokens::$emptyTokens) === true) {
                $error = 'Space after closing parenthesis of function call prohibited';
                $phpcsFile->addError($error, $closeBracket, 'SpaceAfterCloseBracket');
            }
        }

        // Check if this is a single line or multi-line function call.
        if ($tokens[$openBracket]['line'] === $tokens[$closeBracket]['line']) {
            $this->processSingleLineCall($phpcsFile, $stackPtr, $openBracket, $tokens);
        } else {
            $this->processMultiLineCall($phpcsFile, $stackPtr, $openBracket, $tokens);
        }

    }//end process()


    /**
     * Processes single-line calls.
     *
     * @param PHP_CodeSniffer_File $phpcsFile   The file being scanned.
     * @param int                  $stackPtr    The position of the current token
     *                                          in the stack passed in $tokens.
     * @param int                  $openBracket The position of the openning bracket
     *                                          in the stack passed in $tokens.
     * @param array                $tokens      The stack of tokens that make up
     *                                          the file.
     *
     * @return void
     */
    public function processSingleLineCall(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $openBracket, $tokens)
    {
        $tokenPlus1 = $tokens[($openBracket + 1)]['code'];

        if ($tokenPlus1 === T_CLOSE_PARENTHESIS) {
            return;
        }
        if ($tokenPlus1 !== T_WHITESPACE) {
            $phpcsFile->addError('A space is required after opening parenthesis of function call', $stackPtr, 'NoSpaceAfterOpenBracket');
        }

        $closer = $tokens[$openBracket]['parenthesis_closer'];

        if ($tokens[($closer - 1)]['code'] !== T_WHITESPACE) {
            $phpcsFile->addError('A space is required before closing parenthesis of function call', $closer, 'NoSpaceBeforeCloseBracket');
        } elseif ($closer - 2 === $openBracket) {
            $phpcsFile->addError('Without arguments, space are forbidden in function call', $closer, 'NoSpaceBeforeCloseBracket');
        }

    }//end processSingleLineCall()


    /**
     * Processes multi-line calls.
     *
     * @param PHP_CodeSniffer_File $phpcsFile   The file being scanned.
     * @param int                  $stackPtr    The position of the current token
     *                                          in the stack passed in $tokens.
     * @param int                  $openBracket The position of the openning bracket
     *                                          in the stack passed in $tokens.
     * @param array                $tokens      The stack of tokens that make up
     *                                          the file.
     *
     * @return void
     */
    public function processMultiLineCall(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $openBracket, $tokens)
    {
        // We need to work out how far indented the function
        // call itself is, so we can work out how far to
        // indent the arguments.
        $functionIndent = 0;
        for ($i = ($stackPtr - 1); $i >= 0; $i--) {
            if ($tokens[$i]['line'] !== $tokens[$stackPtr]['line']) {
                $i++;
                break;
            }
        }

        if ($tokens[$i]['code'] === T_WHITESPACE) {
            $functionIndent = strlen($tokens[$i]['content']);
        }

        // Each line between the parenthesis should be indented 4 spaces.
        $closeBracket = $tokens[$openBracket]['parenthesis_closer'];
        $lastLine     = $tokens[$openBracket]['line'];
        for ($i = ($openBracket + 1); $i < $closeBracket; $i++) {
            // Skip nested function calls.
            if ($tokens[$i]["code"] === T_OPEN_PARENTHESIS) {
                $i        = $tokens[$i]['parenthesis_closer'];
                $lastLine = $tokens[$i]['line'];
                continue;
            }
            $code = $tokens[$i]["code"];
            $line = $tokens[$i]["line"];

            if ($line !== $lastLine) {
                $lastLine = $line;

                // We changed lines, so this should be a whitespace indent token.
                if (
                    // Ignore heredoc indentation.
                    in_array($code, PHP_CodeSniffer_Tokens::$heredocTokens) ||
                    // Ignore multi-line string indentation.
                    (in_array($code, PHP_CodeSniffer_Tokens::$stringTokens) && $code === $tokens[$i - 1]['code']) ||
                    // Multi-line comment
                    ($code === T_COMMENT && $tokens[$i - 1]["code"] === T_COMMENT)
                ) {
                    continue;
                }

                if ($line === $tokens[$closeBracket]['line']) {
                    // Closing brace needs to be indented to the same level
                    // as the function call.
                    $expectedIndent = $functionIndent;
                } else {
                    $expectedIndent = ($functionIndent + 4);
                }

                if ($tokens[$i + 1]['code'] === T_OBJECT_OPERATOR) {
                    $expectedIndent += 4;
                }
                if ($tokens[$i - 1]['code'] === T_WHITESPACE && $tokens[$i - 1]['content'] === "\n") {
                    $start = 0;
                    switch ($tokens[$i - 2]["code"]) {
                        case T_INLINE_THEN:
                        case T_DOUBLE_ARROW:
                            $start = 4;
                        case T_INLINE_ELSE:
                        case T_STRING_CONCAT:
                            $linePrev = $tokens[$i - 1]["line"];
                            for ( $j = $i - 1; $j >=0 && $tokens[$j]["line"] >= $linePrev; --$j) {
                            }
                            $expectedIndent = $start;
                            if ($tokens[$j+1]["type"] === "T_WHITESPACE")
                                $expectedIndent += strlen($tokens[$j+1]["content"]);
                    }
                }

                if ($code !== T_WHITESPACE) {
                    $foundIndent = 0;
                } else {
                    $j = 0;
                    while ($tokens[$i + $j]['content'] === "\n") {
                        ++$j;
                        if ( $code !== T_WHITESPACE) {
                            $phpcsFile->addError("Multi-line function call not indented correctly; expected %s spaces but found newlines instead", $i, 'Indent', array($expectedIndent));
                            break;
                        }
                    }
                    $foundIndent = strlen($tokens[$i + $j]['content']);
                }

                if ($expectedIndent !== $foundIndent) {
                    $error = 'Multi-line function call not indented correctly; expected %s spaces but found %s';
                    $data  = array(
                              $expectedIndent,
                              $foundIndent,
                             );
                    $phpcsFile->addError($error, $i, 'Indent', $data);
                }
            }//end if

            // Skip the rest of a closure.
            if ($code === T_CLOSURE) {
                $i        = $tokens[$i]['scope_closer'];
                $lastLine = $tokens[$i]['line'];
                continue;
            }
        }//end for

        if ($tokens[($openBracket + 1)]['content'] !== $phpcsFile->eolChar) {
            $error = 'Opening parenthesis of a multi-line function call must be the last content on the line';
            $phpcsFile->addError($error, $stackPtr, 'ContentAfterOpenBracket');
        }

        $prev = $phpcsFile->findPrevious(T_WHITESPACE, ($closeBracket - 1), null, true);
        if ($tokens[$prev]['line'] === $tokens[$closeBracket]['line']) {
            $error = 'Closing parenthesis of a multi-line function call must be on a line by itself';
            $phpcsFile->addError($error, $closeBracket, 'CloseBracketLine');
        }

    }//end processMultiLineCall()


}//end class
?>
