<?php
class ezpnext_Sniffs_Files_NoMultipleImportSniff implements PHP_CodeSniffer_Sniff
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

        // Searching for a previous "use" keyword
        if ($phpcsFile->findPrevious(T_USE, ($stackPtr - 1)) !== false) {
            // Detecting "use" keyword of clusures
            if (($prevPtr = $phpcsFile->findPrevious(T_CLOSE_PARENTHESIS, ($stackPtr - 1))) !== false) {
                if ($tokens[$tokens[$prevPtr]["parenthesis_owner"]]["code"] === T_CLOSURE) {
                    return;
                }
            }
            $phpcsFile->addError('Only one "use" keyword must be used to import classes and namespaces.', $stackPtr, 'NoMultipleUseKeyword');
        }
    }
}
?>
