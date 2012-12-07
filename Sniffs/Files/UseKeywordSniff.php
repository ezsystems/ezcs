<?php
class ezcs_Sniffs_Files_UseKeywordSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array( T_USE );
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if ( $tokens[$stackPtr + 1]["type"] === "T_WHITESPACE" && $tokens[$stackPtr + 1]["content"] !== " " ) {
            $phpcsFile->addError('The "use" keyword must be followed by one single space.', $stackPtr, 'NoMultilineUseKeyword');
        }

        // "use" keyword is the one of a closure, skipping next checks
        if ( ( $previousFunctionKeyword = $phpcsFile->findPrevious(T_CLOSURE, $stackPtr - 1 ) ) !== false && $tokens[$previousFunctionKeyword]["parenthesis_closer"] < $stackPtr && $stackPtr < $tokens[$previousFunctionKeyword]["scope_closer"] )
            return;

        if ( $phpcsFile->findNext(T_COMMA, $stackPtr + 1, $nextSemicolon = $phpcsFile->findNext(T_SEMICOLON, $stackPtr + 1) ) !== false ) {
            $phpcsFile->addError('The "use" keyword must be used to import one class/namespace at a time.', $stackPtr, 'NoMultipleImportsPerUseKeyword');
        }
        if ( ( $previousUseKeyword = $phpcsFile->findPrevious(T_USE, $stackPtr - 1 ) ) !== false && $tokens[$previousUseKeyword]["line"] === $tokens[$stackPtr]["line"] ) {
            $phpcsFile->addError('Two "use" keywords must not appear on the same line.', $stackPtr, 'NoMultilineUseKeyword');
        }
        if ( $tokens[$nextSemicolon]["line"] !== $tokens[$stackPtr]["line"] ) {
            $phpcsFile->addError('The "use" keyword must not be spread over more than one line.', $stackPtr, 'NoMultilineUseKeyword');
        }
    }
}
