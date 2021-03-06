<?php

class FlaggedRevsLog {
	/**
	 * @param string $action A valid review log action
	 * @return bool
	 */
	public static function isReviewAction( $action ) {
		return preg_match( '/^(approve2?(-i|-a|-ia)?|unapprove2?)$/', $action );
	}

	/**
	 * @param string $action A valid stability log action
	 * @return bool
	 */
	public static function isStabilityAction( $action ) {
		return preg_match( '/^(config|modify|reset)$/', $action );
	}

	/**
	 * $action is a valid review log deprecate action
	 * @param string $action
	 * @return bool
	 */
	public static function isReviewDeapproval( $action ) {
		return ( $action == 'unapprove' || $action == 'unapprove2' );
	}

	/**
	 * Record a log entry on the review action
	 * @param Title $title
	 * @param array $dims
	 * @param array $oldDims
	 * @param string $comment
	 * @param int $revId, revision ID
	 * @param int $stableId, prior stable revision ID
	 * @param bool $approve, approved? (otherwise unapproved)
	 * @param bool $auto
	 */
	public static function updateReviewLog(
		Title $title, array $dims, array $oldDims,
		$comment, $revId, $stableId, $approve, $auto = false
	) {
		$log = new LogPage( 'review',
			false /* $rc */,
			$auto ? "skipUDP" : "UDP" // UDP logging
		);
		# Tag rating list (e.g. accuracy=x, depth=y, style=z)
		$ratings = array();
		# Skip rating list if flagging is just an 0/1 feature...
		if ( !FlaggedRevs::binaryFlagging() ) {
			// Give grep a chance to find the usages:
			// revreview-accuracy, revreview-depth, revreview-style,
			// revreview-accuracy-0, revreview-accuracy-1, revreview-accuracy-2, revreview-accuracy-3, revreview-accuracy-4,
			// revreview-depth-0, revreview-depth-1, revreview-depth-2, revreview-depth-3, revreview-depth-4,
			// revreview-style-0, revreview-style-1, revreview-style-2, revreview-style-3, revreview-style-4
			foreach ( $dims as $quality => $level ) {
				$ratings[] = wfMessage( "revreview-$quality" )->inContentLanguage()->text() .
					wfMessage( 'colon-separator' )->inContentLanguage()->text() .
					wfMessage( "revreview-$quality-$level" )->inContentLanguage()->text();
			}
		}
		$isAuto = ( $auto && !FlaggedRevs::isQuality( $dims ) ); // Paranoid check
		// Approved revisions
		if ( $approve ) {
			if ( $isAuto ) {
				$comment = wfMessage( 'revreview-auto' )->inContentLanguage()->text(); // override this
			}
			# Make comma-separated list of ratings
			$rating = !empty( $ratings )
				? '[' . implode( ', ', $ratings ) . ']'
				: '';
			# Append comment with ratings
			if ( $rating != '' ) {
				$comment .= $comment ? " $rating" : $rating;
			}
			# Sort into the proper action (useful for filtering)
			$action = ( FlaggedRevs::isQuality( $dims ) || FlaggedRevs::isQuality( $oldDims ) ) ?
				'approve2' : 'approve';
			if ( !$stableId ) { // first time
				$action .= $isAuto ? "-ia" : "-i";
			} elseif ( $isAuto ) { // automatic
				$action .= "-a";
			}
		// De-approved revisions
		} else {
			$action = FlaggedRevs::isQuality( $oldDims ) ?
				'unapprove2' : 'unapprove';
		}
		$ts = Revision::getTimestampFromId( $title, $revId );
		# Param format is <rev id, old stable id, rev timestamp>
		$logid = $log->addEntry( $action, $title, $comment, array( $revId, $stableId, $ts ) );
		# Make log easily searchable by rev_id
		$log->addRelations( 'rev_id', array( $revId ), $logid );
	}

	/**
	 * Record a log entry on the stability config change action
	 * @param Title $title
	 * @param array $config
	 * @param array $oldConfig
	 * @param string $reason
	 */
	public static function updateStabilityLog(
		Title $title, array $config, array $oldConfig, $reason
	) {
		$log = new LogPage( 'stable' );
		if ( FRPageConfig::configIsReset( $config ) ) {
			# We are going back to default settings
			$log->addEntry( 'reset', $title, $reason );
		} else {
			# We are changing to non-default settings
			$action = ( $oldConfig === FRPageConfig::getDefaultVisibilitySettings() )
				? 'config' // set a custom configuration
				: 'modify'; // modified an existing custom configuration
			$log->addEntry( $action, $title, $reason,
				FlaggedRevsLog::collapseParams( self::stabilityLogParams( $config ) ) );
		}
	}

	/**
	 * Get log params (associate array) from a stability config
	 * @param array $config
	 * @return array (associative)
	 */
	public static function stabilityLogParams( array $config ) {
		$params = $config;
		if ( !FlaggedRevs::useOnlyIfProtected() ) {
			$params['precedence'] = 1; // b/c hack for presenting log params...
		}
		return $params;
	}

	/**
	 * Collapse an associate array into a string
	 * @param array $pars
	 * @throws Exception
	 * @return string
	 */
	public static function collapseParams( array $pars ) {
		$res = array();
		foreach ( $pars as $param => $value ) {
			// Sanity check...
			if ( strpos( $param, '=' ) !== false || strpos( $value, '=' ) !== false ) {
				throw new Exception( "collapseParams() - cannot use equal sign" );
			} elseif ( strpos( $param, "\n" ) !== false || strpos( $value, "\n" ) !== false ) {
				throw new Exception( "collapseParams() - cannot use newline" );
			}
			$res[] = "{$param}={$value}";
		}
		return implode( "\n", $res );
	}

	/**
	 * Expand a list of log params into an associative array
	 * @param array $pars
	 * @return array (associative)
	 */
	public static function expandParams( array $pars ) {
		$res = array();
		$pars = array_filter( $pars, 'strlen' );
		foreach ( $pars as $paramAndValue ) {
			list( $param, $value ) = explode( '=', $paramAndValue, 2 );
			$res[$param] = $value;
		}
		return $res;
	}
}
