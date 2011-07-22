<?php
class ezpnext_Sniffs_Files_NoNewlineAtEOFSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array( T_OPEN_TAG,);

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

        // Make sure this is the first PHP open tag so we don't process the same file twice.
        if ($phpcsFile->findPrevious(T_OPEN_TAG, ($stackPtr - 1)) !== false) {
            return;
        }

        $lastToken = end($tokens);

        if (substr($lastToken["content"], -1) !== "\n") {
            $phpcsFile->addError("No newline at EOF", key($tokens), 'NoNewlineEOF');
        }
    }
}
?>
