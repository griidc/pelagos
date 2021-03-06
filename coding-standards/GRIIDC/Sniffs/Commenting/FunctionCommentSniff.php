<?php
namespace GRIIDC\Sniffs\Commenting;

use PHP_CodeSniffer\Standards\Squiz\Sniffs\Commenting\FunctionCommentSniff as SquizFunctionCommentSniff;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Extends Squiz's function DocBlock rules.
 *
 * Extends Squiz's function DocBlock rules to:
 *   - require the @return tag to be the last tag in the DocBlock
 *
 */
class FunctionCommentSniff extends SquizFunctionCommentSniff
{


    /**
     * Process the return comment of this function comment.
     *
     * @param PHP_CodeSniffer_File $phpcsFile    The file being scanned.
     * @param int                  $stackPtr     The position of the current token
     *                                           in the stack passed in $tokens.
     * @param int                  $commentStart The position in the stack where the comment started.
     *
     * @return void
     */
    protected function processReturn(File $phpcsFile, $stackPtr, $commentStart)
    {
        parent::processReturn($phpcsFile, $stackPtr, $commentStart);

        $tokens = $phpcsFile->getTokens();

        // Skip constructor and destructor.
        $methodName      = $phpcsFile->getDeclarationName($stackPtr);
        $isSpecialMethod = ($methodName === '__construct' || $methodName === '__destruct');

        $return = null;
        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
            if ($tokens[$tag]['content'] === '@return') {
                $return = $tag;
            }
        }

        if ($isSpecialMethod === true) {
            return;
        }

        if ($return !== null) {
            $commentEnd = $tokens[$commentStart]['comment_closer'];
            $nextTag = $phpcsFile->findNext(T_DOC_COMMENT_TAG, ($return + 1), ($commentEnd - 1), false);
            if ($nextTag !== false) {
                $error = '@return tag must be the last tag in the comment block';
                $phpcsFile->addError($error, $return, 'MisplacedReturn');
            }
        }//end if

    }//end processReturn()


}//end class
