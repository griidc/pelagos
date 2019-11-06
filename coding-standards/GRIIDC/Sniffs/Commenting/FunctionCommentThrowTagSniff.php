<?php
/**
 * Verifies that a @throws tag exists for a function that throws exceptions.
 * Verifies the number of @throws tags and the number of throw tokens matches.
 * Verifies the exception type.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 */

if (class_exists('PHP_CodeSniffer_Standards_AbstractScopeSniff', true) === false) {
    $error = 'Class PHP_CodeSniffer_Standards_AbstractScopeSniff not found';
    throw new PHP_CodeSniffer_Exception($error);
}

/**
 * Verifies that a @throws tag exists for a function that throws exceptions.
 * Verifies the number of @throws tags and the number of throw tokens matches.
 * Verifies the exception type.
 *
 * Based on Squiz_Sniffs_Commenting_FunctionCommentThrowTagSniff
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 */
class GRIIDC_Sniffs_Commenting_FunctionCommentThrowTagSniff extends PHP_CodeSniffer_Standards_AbstractScopeSniff
{


    /**
     * Constructs a Squiz_Sniffs_Commenting_FunctionCommentThrowTagSniff.
     */
    public function __construct()
    {
        parent::__construct(array(T_FUNCTION), array(T_THROW));

    }//end __construct()


    /**
     * Processes the function tokens within the class.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param int                  $stackPtr  The position where the token was found.
     * @param int                  $currScope The current scope opener token.
     *
     * @return void
     */
    protected function processTokenWithinScope(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $currScope)
    {
        // Is this the first throw token within the current function scope?
        // If so, we have to validate other throw tokens within the same scope.
        $previousThrow = $phpcsFile->findPrevious(T_THROW, ($stackPtr - 1), $currScope);
        if ($previousThrow !== false) {
            return;
        }

        $tokens = $phpcsFile->getTokens();

        $find = array(
                 T_COMMENT,
                 T_DOC_COMMENT_CLOSE_TAG,
                 T_CLASS,
                 T_FUNCTION,
                 T_OPEN_TAG,
                );

        $commentEnd = $phpcsFile->findPrevious($find, ($currScope - 1));
        if ($commentEnd === false) {
            return;
        }

        if ($tokens[$commentEnd]['code'] !== T_DOC_COMMENT_CLOSE_TAG) {
            // Function doesn't have a comment. Let someone else warn about that.
            return;
        }

        // Find the position where the current function scope ends.
        $currScopeEnd = 0;
        if (isset($tokens[$currScope]['scope_closer']) === true) {
            $currScopeEnd = $tokens[$currScope]['scope_closer'];
        }

        // Find all the exception type token within the current scope.
        $throwTokens = array();
        $currPos     = $stackPtr;
        if ($currScopeEnd !== 0) {
            while ($currPos < $currScopeEnd && $currPos !== false) {

                $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($currPos + 1), null, true);
                if ($tokens[$nextToken]['code'] === T_NEW) {
                    $currException = $phpcsFile->findNext(
                        array(
                         T_NS_SEPARATOR,
                         T_STRING,
                        ),
                        $currPos,
                        $currScopeEnd,
                        false,
                        null,
                        true
                    );

                    if ($currException !== false) {
                        $endException = $phpcsFile->findNext(
                            array(
                             T_NS_SEPARATOR,
                             T_STRING,
                            ),
                            ($currException + 1),
                            $currScopeEnd,
                            true,
                            null,
                            true
                        );

                        if ($endException === false) {
                            $throwTokens[] = $tokens[$currException]['content'];
                        } else {
                            $throwTokens[] = $phpcsFile->getTokensAsString($currException, ($endException - $currException));
                        }
                    }//end if
                } elseif ($tokens[$nextToken]['code'] === T_VARIABLE) {
                    /*
                        If we can't find a NEW, and we are throwing
                        a variable, we add a "wildcard" since we
                        don't know the exception class.
                    */
                    $throwTokens[] = '*';
                } elseif ($tokens[$nextToken]['code'] === T_STRING or $tokens[$nextToken]['code'] === T_NS_SEPARATOR) {
                    $endException = $phpcsFile->findNext(
                        array(
                         T_NS_SEPARATOR,
                         T_STRING,
                         T_DOUBLE_COLON,
                        ),
                        ($nextToken + 1),
                        $currScopeEnd,
                        true,
                        null,
                        true
                    );
                    $throwTokens[] = $phpcsFile->getTokensAsString($nextToken, ($endException - $nextToken));
                }//end if

                $currPos = $phpcsFile->findNext(T_THROW, ($currPos + 1), $currScopeEnd);
            }//end while
        }//end if

        $throwTags    = array();
        $commentStart = $tokens[$commentEnd]['comment_opener'];
        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
            if ($tokens[$tag]['content'] !== '@throws') {
                continue;
            }

            if ($tokens[($tag + 2)]['code'] === T_DOC_COMMENT_STRING) {
                $exception = $tokens[($tag + 2)]['content'];
                $space     = strpos($exception, ' ');
                if ($space !== false) {
                    $exception = substr($exception, 0, $space);
                }

                if (array_key_exists($exception, $throwTags)) {
                    $throwTags[$exception]++;
                } else {
                    $throwTags[$exception] = 1;
                }
            }
        }

        if (empty($throwTags) === true) {
            $error = 'Missing @throws tag in function comment';
            $phpcsFile->addError($error, $commentEnd, 'Missing');
            return;
        } else if (empty($throwTokens) === true) {
            // If token count is zero, it means that only variables are being
            // thrown, so we need at least one @throws tag (checked above).
            // Nothing more to do.
            return;
        }

        // Make sure @throws tag count matches throw token count.
        $tokenCount = count($throwTokens);
        $tagCount   = array_sum($throwTags);
        if ($tokenCount !== $tagCount) {
            $error = 'Expected %s @throws tag(s) in function comment; %s found';
            $data  = array(
                      $tokenCount,
                      $tagCount,
                     );
            $phpcsFile->addError($error, $commentEnd, 'WrongNumber', $data);
            return;
        }

        // Count throw tokens of each type of throw.
        $throwTokenCounts = array();
        foreach ($throwTokens as $throw) {
            if (array_key_exists($throw, $throwTokenCounts)) {
                $throwTokenCounts[$throw]++;
            } else {
                $throwTokenCounts[$throw] = 1;
            }
        }

        // Calculate the difference between throw tokens of each type and throw tags of each type.
        $diffTags = array();
        foreach ($throwTokenCounts as $throw => $count) {
            if ($throw !== '*') {
                $diffTags[$throw] = array_key_exists($throw, $throwTags) ? ($throwTags[$throw] - $count) : (-$count);
            }
        }

        $errorThrows = array();
        // Check tags match tokens.
        foreach ($throwTags as $throw => $count) {
            if (array_key_exists($throw, $throwTokenCounts) && $throwTokenCounts[$throw] !== $count) {
                // If we encounter a mismatch between number of tokens and tags for a given type.
                $diff = ($throwTags[$throw] - $throwTokenCounts[$throw]);
                if ($diff > 0 &&
                    array_key_exists('*', $throwTokenCounts) &&
                    $throwTokenCounts['*'] > 0  &&
                    ($throwTokenCounts['*'] - $diff) >= 0 &&
                    ($diffTags[$throw] - $diff) >= 0) {
                    // If we have more tags of a given type than tokens,
                    // and enough remaining "wildcard" tokens,
                    // and enough remaining of this type in diffTags.
                    // Subtract from the wildcard token count the difference.
                    $throwTokenCounts['*'] -= $diff;
                    // And subtract from diffTags the difference.
                    $diffTags[$throw] -= $diff;
                } else {
                    // Report the mismatch.
                    // Mark that we're reporting an error for this throw.
                    $errorThrows[$throw] = true;
                    $error = 'Expected %s @throws tag for "%s" exception; %s found';
                    $data  = array(
                        ($throwTokenCounts[$throw] + (array_key_exists('*', $throwTokenCounts) ? $throwTokenCounts['*'] : 0)),
                        $throw,
                        $throwTags[$throw],
                    );
                    $phpcsFile->addError($error, $commentEnd, 'Missing', $data);
                }
            }
        }

        // Check tokens match tags.
        foreach ($diffTags as $throw => $count) {
            if ($count !== 0 and !array_key_exists($throw, $errorThrows)) {
                // If the counts don't match and we haven't already reported this.
                $error = 'Expected %s @throws tag for "%s" exception; %s found';
                $data  = array(
                    $throwTokenCounts[$throw],
                    $throw,
                    array_key_exists($throw, $throwTags) ? $throwTags[$throw] : 0,
                );
                $phpcsFile->addError($error, $commentEnd, 'Missing', $data);
            }
        }

    }//end processTokenWithinScope()


}//end class
