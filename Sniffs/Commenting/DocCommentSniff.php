<?php
/**
 * Extends the basic DocBlock formatting rules.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 */

if (class_exists('Generic_Sniffs_Commenting_DocCommentSniff', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class Generic_Sniffs_Commenting_DocCommentSniff not found');
}

/**
 * Extends the basic DocBlock formatting rules.
 *
 * Extends the basic DocBlock formatting rules to:
 *  - only allow the short description to be on a single line
 *  - require the short description to end with a full stop
 *  - require a single blank line after the short description and before the long description
 *  - require the long description to start with a capital letter
 *
 * Based on Generic_Sniffs_Commenting_DocCommentSniff.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 */
class GRIIDC_Sniffs_Commenting_DocCommentSniff extends Generic_Sniffs_Commenting_DocCommentSniff
{


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
        parent::process($phpcsFile, $stackPtr);
        $tokens       = $phpcsFile->getTokens();
        $commentStart = $stackPtr;
        $commentEnd   = $tokens[$stackPtr]['comment_closer'];

        $empty = array(
                  T_DOC_COMMENT_WHITESPACE,
                  T_DOC_COMMENT_STAR,
                 );

        $short = $phpcsFile->findNext($empty, ($stackPtr + 1), $commentEnd, true);
        if ($short === false) {
            // No content at all.
            $error = 'Doc comment is empty';
            $phpcsFile->addError($error, $stackPtr, 'Empty');
            return;
        }

        $shortContent = $tokens[$short]['content'];

        $lastChar = substr($shortContent, -1);
        if ($lastChar !== '.') {
            $error = 'Doc comment short description must end with a full stop';
            $phpcsFile->addError($error, ($short), 'ShortNoFullStop');
        }

        $long = $phpcsFile->findNext($empty, ($short + 1), ($commentEnd - 1), true);
        if ($long === false) {
            return;
        }

        if ($tokens[$long]['code'] === T_DOC_COMMENT_STRING) {
            if ($tokens[$long]['line'] !== ($tokens[$short]['line'] + 2)) {
                $error = 'There must be exactly one blank line between descriptions in a doc comment';
                $fix   = $phpcsFile->addFixableError($error, $long, 'SpacingBetween');
                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();
                    for ($i = ($short + 1); $i < $long; $i++) {
                        if ($tokens[$i]['line'] === $tokens[$short]['line']) {
                            continue;
                        } else if ($tokens[$i]['line'] === ($tokens[$long]['line'] - 1)) {
                            break;
                        }

                        $phpcsFile->fixer->replaceToken($i, '');
                    }

                    $phpcsFile->fixer->endChangeset();
                }
            }

            if (preg_match('/\p{Lu}|\P{L}/u', $tokens[$long]['content'][0]) === 0) {
                $error = 'Doc comment long description must start with a capital letter';
                $phpcsFile->addError($error, $long, 'LongNotCapital');
            }
        }//end if

    }//end process()


}//end class
