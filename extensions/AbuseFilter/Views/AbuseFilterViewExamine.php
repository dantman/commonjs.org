<?php

class AbuseFilterViewExamine extends AbuseFilterView {
	public static $examineType = null;
	public static $examineId = null;

	public $mCounter, $mSearchUser, $mSearchPeriodStart, $mSearchPeriodEnd,
		$mTestFilter;

	function show() {
		$out = $this->getOutput();
		$out->setPageTitle( $this->msg( 'abusefilter-examine' ) );
		$out->addWikiMsg( 'abusefilter-examine-intro' );

		$this->loadParameters();

		// Check if we've got a subpage
		if ( count( $this->mParams ) > 1 && is_numeric( $this->mParams[1] ) ) {
			$this->showExaminerForRC( $this->mParams[1] );
		} elseif ( count( $this->mParams ) > 2
			&& $this->mParams[1] == 'log'
			&& is_numeric( $this->mParams[2] ) )
		{
			$this->showExaminerForLogEntry( $this->mParams[2] );
		} else {
			$this->showSearch();
		}
	}

	function showSearch() {
		// Add selector
		$selector = '';

		$selectFields = array(); # Same fields as in Test
		$selectFields['abusefilter-test-user'] = Xml::input( 'wpSearchUser', 45, $this->mSearchUser );
		$selectFields['abusefilter-test-period-start'] =
			Xml::input( 'wpSearchPeriodStart', 45, $this->mSearchPeriodStart );
		$selectFields['abusefilter-test-period-end'] =
			Xml::input( 'wpSearchPeriodEnd', 45, $this->mSearchPeriodEnd );

		$selector .= Xml::buildForm( $selectFields, 'abusefilter-examine-submit' );
		$selector .= Html::hidden( 'submit', 1 );
		$selector .= Html::hidden( 'title', $this->getTitle( 'examine' )->getPrefixedDBkey() );
		$selector = Xml::tags( 'form',
			array(
				'action' => $this->getTitle( 'examine' )->getLocalURL(),
				'method' => 'get'
			),
			$selector
		);
		$selector = Xml::fieldset(
			$this->msg( 'abusefilter-examine-legend' )->text(),
			$selector
		);
		$this->getOutput()->addHTML( $selector );

		if ( $this->mSubmit ) {
			$this->showResults();
		}
	}

	function showResults() {
		$changesList = new AbuseFilterChangesList( $this->getSkin() );
		$output = $changesList->beginRecentChangesList();
		$this->mCounter = 1;

		$pager = new AbuseFilterExaminePager( $this, $changesList );

		$output .= $pager->getNavigationBar() .
					$pager->getBody() .
					$pager->getNavigationBar();

		$output .= $changesList->endRecentChangesList();

		$this->getOutput()->addHTML( $output );
	}

	function showExaminerForRC( $rcid ) {
		// Get data
		$dbr = wfGetDB( DB_SLAVE );
		$row = $dbr->selectRow( 'recentchanges', '*', array( 'rc_id' => $rcid ), __METHOD__ );
		$out = $this->getOutput();
		if ( !$row ) {
			$out->addWikiMsg( 'abusefilter-examine-notfound' );
			return;
		}

		self::$examineType = 'rc';
		self::$examineId = $rcid;

		$vars = AbuseFilter::getVarsFromRCRow( $row );
		$out->addJsConfigVars( 'wgAbuseFilterVariables', $vars->dumpAllVars( true ) );
		$this->showExaminer( $vars );
	}

	function showExaminerForLogEntry( $logid ) {
		// Get data
		$dbr = wfGetDB( DB_SLAVE );
		$row = $dbr->selectRow( 'abuse_filter_log', '*', array( 'afl_id' => $logid ), __METHOD__ );
		$out = $this->getOutput();

		if ( !$row ) {
			$out->addWikiMsg( 'abusefilter-examine-notfound' );
			return;
		}

		self::$examineType = 'log';
		self::$examineId = $logid;

		if ( !SpecialAbuseLog::canSeeDetails( $row->afl_filter ) ) {
			$out->addWikiMsg( 'abusefilter-log-cannot-see-details' );
			return;
		}

		if ( $row->afl_deleted && !SpecialAbuseLog::canSeeHidden() ) {
			$out->addWikiMsg( 'abusefilter-log-details-hidden' );
			return;
		}

		$vars = AbuseFilter::loadVarDump( $row->afl_var_dump );
		$out->addJsConfigVars( 'wgAbuseFilterVariables', $vars->dumpAllVars( true ) );
		$this->showExaminer( $vars );
	}

	function showExaminer( $vars ) {
		$output = $this->getOutput();

		if ( !$vars ) {
			$output->addWikiMsg( 'abusefilter-examine-incompatible' );
			return;
		}

		if ( $vars instanceof AbuseFilterVariableHolder ) {
			$vars = $vars->exportAllVars();
		}

		$html = '';

		$output->addModules( 'ext.abuseFilter.examine' );

		// Add test bit
		if ( $this->getUser()->isAllowed( 'abusefilter-modify' ) ) {
			$tester = Xml::tags( 'h2', null, $this->msg( 'abusefilter-examine-test' )->parse() );
			$tester .= AbuseFilter::buildEditBox( $this->mTestFilter, 'wpTestFilter', false );
			$tester .=
				"\n" .
				Xml::inputLabel(
					$this->msg( 'abusefilter-test-load-filter' )->text(),
					'wpInsertFilter',
					'mw-abusefilter-load-filter',
					10,
					''
				) .
				'&#160;' .
				Xml::element(
					'input',
					array(
						'type' => 'button',
						'value' => $this->msg( 'abusefilter-test-load' )->text(),
						'id' => 'mw-abusefilter-load'
					)
				);
			$html .= Xml::tags( 'div', array( 'id' => 'mw-abusefilter-examine-editor' ), $tester );
			$html .= Xml::tags( 'p',
				null,
				Xml::element( 'input',
					array(
						'type' => 'button',
						'value' => $this->msg( 'abusefilter-examine-test-button' )->text(),
						'id' => 'mw-abusefilter-examine-test'
					)
				) .
				Xml::element( 'div',
					array(
						'id' => 'mw-abusefilter-syntaxresult',
						'style' => 'display: none;'
					), '&#160;'
				)
			);
		}

		// Variable dump
		$html .= Xml::tags(
			'h2',
			null,
			$this->msg( 'abusefilter-examine-vars', 'parseinline' )->parse()
		);
		$html .= AbuseFilter::buildVarDumpTable( $vars );

		$output->addHTML( $html );
	}

	function loadParameters() {
		$request = $this->getRequest();
		$searchUsername = $request->getText( 'wpSearchUser' );
		$this->mSearchPeriodStart = $request->getText( 'wpSearchPeriodStart' );
		$this->mSearchPeriodEnd = $request->getText( 'wpSearchPeriodEnd' );
		$this->mSubmit = $request->getCheck( 'submit' );
		$this->mTestFilter = $request->getText( 'testfilter' );

		// Normalise username
		$userTitle = Title::newFromText( $searchUsername );

		if ( $userTitle && $userTitle->getNamespace() == NS_USER ) {
			$this->mSearchUser = $userTitle->getText(); // Allow User:Blah syntax.
		} elseif ( $userTitle ) {
			// Not sure of the value of prefixedText over text, but no need to munge unnecessarily.
			$this->mSearchUser = $userTitle->getPrefixedText();
		} else {
			$this->mSearchUser = '';
		}
	}
}

class AbuseFilterExaminePager extends ReverseChronologicalPager {
	/**
	 * @param AbuseFilterViewExamine $page
	 * @param AbuseFilterChangesList $changesList
	 */
	function __construct( $page, $changesList ) {
		parent::__construct();
		$this->mChangesList = $changesList;
		$this->mPage = $page;
	}

	function getQueryInfo() {
		$dbr = wfGetDB( DB_SLAVE );
		$conds = array(
			'rc_user_text' => $this->mPage->mSearchUser,
			'rc_type != ' . RC_EXTERNAL
		);

		$startTS = strtotime( $this->mPage->mSearchPeriodStart );
		if ( $startTS ) {
			$conds[] = 'rc_timestamp>=' . $dbr->addQuotes( $dbr->timestamp( $startTS ) );
		}
		$endTS = strtotime( $this->mPage->mSearchPeriodEnd );
		if ( $endTS ) {
			$conds[] = 'rc_timestamp<=' . $dbr->addQuotes( $dbr->timestamp( $endTS ) );
		}

		// If one of these is true, we're abusefilter compatible.
		$compatConds = array(
			'rc_this_oldid != 0',
			'rc_log_action' => array( 'move', 'create' ),
		);

		$conds[] = $dbr->makeList( $compatConds, LIST_OR );

		$info = array(
			'tables' => 'recentchanges',
			'fields' => '*',
			'conds' => array_filter( $conds ),
			'options' => array( 'ORDER BY' => 'rc_timestamp DESC' ),
		);

		return $info;
	}

	function formatRow( $row ) {
		# Incompatible stuff.
		$rc = RecentChange::newFromRow( $row );
		$rc->counter = $this->mPage->mCounter++;
		return $this->mChangesList->recentChangesLine( $rc, false );
	}

	function getIndexField() {
		return 'rc_id';
	}

	function getTitle() {
		return $this->mPage->getTitle( 'examine' );
	}

	function getEmptyBody() {
		return $this->msg( 'abusefilter-examine-noresults' )->parseAsBlock();
	}
}
