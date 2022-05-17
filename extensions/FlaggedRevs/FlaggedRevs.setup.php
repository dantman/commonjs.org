<?php
/**
 * Class containing basic setup functions.
 * This class depends on config variables in LocalSettings.php.
 * Note: avoid  FlaggedRevs class calls here for performance (like load.php).
 */
class FlaggedRevsSetup {
	/* Status of whether FlaggedRevs::load() can be called */
	protected static $canLoad = false;

	/**
	 * Signal that LocalSettings.php is loaded.
	 *
	 * @return void
	 */
	public static function setReady() {
		self::$canLoad = true;
	}

	/**
	 * The FlaggedRevs class uses this as a sanity check.
	 *
	 * @return bool
	 */
	public static function isReady() {
		return self::$canLoad;
	}

	/**
	 * Register source code paths.
	 * This function must NOT depend on any config vars.
	 *
	 * @param array $classes $wgAutoloadClasses
	 * @param array $messagesFiles $wgExtensionMessagesFiles
	 * @param array $messagesDirs $wgMessagesDirs
	 * @return void
	 */
	public static function defineSourcePaths( array &$classes, array &$messagesFiles, array &$messagesDirs ) {
		$dir = dirname( __FILE__ );

		# Basic directory layout
		$backendDir       = "$dir/backend";
		$schemaDir        = "$dir/backend/schema";
		$businessDir      = "$dir/business";
		$apiDir           = "$dir/api";
		$apiActionDir     = "$dir/api/actions";
		$apiReportDir     = "$dir/api/reports";
		$frontendDir      = "$dir/frontend";
		$langDir          = "$dir/frontend/language";
		$spActionDir      = "$dir/frontend/specialpages/actions";
		$spReportDir      = "$dir/frontend/specialpages/reports";
		$testDir          = "$dir/tests";

		### Backend classes ###
		# Utility classes...
		$classes['FlaggedRevs'] = "$backendDir/FlaggedRevs.class.php";
		$classes['FRUserCounters'] = "$backendDir/FRUserCounters.php";
		$classes['FRUserActivity'] = "$backendDir/FRUserActivity.php";
		$classes['FRPageConfig'] = "$backendDir/FRPageConfig.php";
		$classes['FlaggedRevsLog'] = "$backendDir/FlaggedRevsLog.php";
		$classes['FRInclusionCache'] = "$backendDir/FRInclusionCache.php";
		$classes['FlaggedRevsStats'] = "$backendDir/FlaggedRevsStats.php";
		# Data access object classes...
		$classes['FRExtraCacheUpdate'] = "$backendDir/FRExtraCacheUpdate.php";
		$classes['FRExtraCacheUpdateJob'] = "$backendDir/FRExtraCacheUpdate.php";
		$classes['FRSquidUpdate'] = "$backendDir/FRExtraCacheUpdate.php";
		$classes['FRDependencyUpdate'] = "$backendDir/FRDependencyUpdate.php";
		$classes['FRInclusionManager'] = "$backendDir/FRInclusionManager.php";
		$classes['FlaggableWikiPage'] = "$backendDir/FlaggableWikiPage.php";
		$classes['FlaggedRevision'] = "$backendDir/FlaggedRevision.php";
		$classes['FRParserCacheStable'] = "$backendDir/FRParserCacheStable.php";
		### End ###

		### Business object classes ###
		$classes['FRGenericSubmitForm'] = "$businessDir/FRGenericSubmitForm.php";
		$classes['RevisionReviewForm'] = "$businessDir/RevisionReviewForm.php";
		$classes['PageStabilityForm'] = "$businessDir/PageStabilityForm.php";
		$classes['PageStabilityGeneralForm'] = "$businessDir/PageStabilityForm.php";
		$classes['PageStabilityProtectForm'] = "$businessDir/PageStabilityForm.php";
		### End ###

		### Presentation classes ###
		# Main i18n file and special page alias file
		$messagesDirs['FlaggedRevs'] = __DIR__ . '/i18n/flaggedrevs';
		$messagesFiles['FlaggedRevs'] = "$langDir/FlaggedRevs.i18n.php";
		$messagesFiles['FlaggedRevsMagic'] = "$langDir/FlaggedRevs.i18n.magic.php";
		$messagesFiles['FlaggedRevsAliases'] = "$langDir/FlaggedRevs.alias.php";
		# UI setup, forms, and HTML elements
		$classes['FlaggedRevsUISetup'] = "$frontendDir/FlaggedRevsUI.setup.php";
		$classes['FlaggablePageView'] = "$frontendDir/FlaggablePageView.php";
		$classes['FlaggedRevsLogView'] = "$frontendDir/FlaggedRevsLogView.php";
		$classes['FlaggedRevsXML'] = "$frontendDir/FlaggedRevsXML.php";
		$classes['RevisionReviewFormUI'] = "$frontendDir/RevisionReviewFormUI.php";
		$classes['RejectConfirmationFormUI'] = "$frontendDir/RejectConfirmationFormUI.php";
		# Revision review UI
		$classes['RevisionReview'] = "$spActionDir/RevisionReview_body.php";
		$messagesDirs['RevisionReview'] = __DIR__ . '/i18n/revisionreview';
		$messagesFiles['RevisionReview'] = "$langDir/RevisionReview.i18n.php";
		# Stable version config UI
		$classes['Stabilization'] = "$spActionDir/Stabilization_body.php";
		$messagesDirs['Stabilization'] = __DIR__ . '/i18n/stabilization';
		$messagesFiles['Stabilization'] = "$langDir/Stabilization.i18n.php";
		# Reviewed versions list
		$classes['ReviewedVersions'] = "$spReportDir/ReviewedVersions_body.php";
		$messagesDirs['ReviewedVersions'] = __DIR__ . '/i18n/reviewedversions';
		$messagesFiles['ReviewedVersions'] = "$langDir/ReviewedVersions.i18n.php";
		# Unreviewed pages list
		$classes['UnreviewedPages'] = "$spReportDir/UnreviewedPages_body.php";
		$messagesDirs['UnreviewedPages'] = __DIR__ . '/i18n/unreviewedpages';
		$messagesFiles['UnreviewedPages'] = "$langDir/UnreviewedPages.i18n.php";
		# Pages with pending changes list
		$classes['PendingChanges'] = "$spReportDir/PendingChanges_body.php";
		$messagesDirs['PendingChanges'] = __DIR__ . '/i18n/pendingchanges';
		$messagesFiles['PendingChanges'] = "$langDir/PendingChanges.i18n.php";
		# Pages with tagged pending changes list
		$classes['ProblemChanges'] = "$spReportDir/ProblemChanges_body.php";
		$messagesDirs['ProblemChanges'] = __DIR__ . '/i18n/problemchanges';
		$messagesFiles['ProblemChanges'] = "$langDir/ProblemChanges.i18n.php";
		# Reviewed pages list
		$classes['ReviewedPages'] = "$spReportDir/ReviewedPages_body.php";
		$messagesDirs['ReviewedPages'] = __DIR__ . '/i18n/reviewedpages';
		$messagesFiles['ReviewedPages'] = "$langDir/ReviewedPages.i18n.php";
		# Stable pages list (for protection config)
		$classes['StablePages'] = "$spReportDir/StablePages_body.php";
		$messagesDirs['StablePages'] = __DIR__ . '/i18n/stablepages';
		$messagesFiles['StablePages'] = "$langDir/StablePages.i18n.php";
		# Configured pages list (non-protection config)
		$classes['ConfiguredPages'] = "$spReportDir/ConfiguredPages_body.php";
		$messagesDirs['ConfiguredPages'] = __DIR__ . '/i18n/configuredpages';
		$messagesFiles['ConfiguredPages'] = "$langDir/ConfiguredPages.i18n.php";
		# Filterable review log page to oversee reviews
		$classes['QualityOversight'] = "$spReportDir/QualityOversight_body.php";
		$messagesDirs['QualityOversight'] = __DIR__ . '/i18n/qualityoversight';
		$messagesFiles['QualityOversight'] = "$langDir/QualityOversight.i18n.php";
		# Review statistics
		$classes['ValidationStatistics'] = "$spReportDir/ValidationStatistics_body.php";
		$messagesDirs['ValidationStatistics'] = __DIR__ . '/i18n/validationstatistics';
		$messagesFiles['ValidationStatistics'] = "$langDir/ValidationStatistics.i18n.php";
		### End ###

		### API classes ###
		# Page review module for API
		$classes['ApiReview'] = "$apiActionDir/ApiReview.php";
		# Page review activity module for API
		$classes['ApiReviewActivity'] = "$apiActionDir/ApiReviewActivity.php";
		# Stability config module for API
		$classes['ApiStabilize'] = "$apiActionDir/ApiStabilize.php";
		$classes['ApiStabilizeGeneral'] = "$apiActionDir/ApiStabilize.php";
		$classes['ApiStabilizeProtect'] = "$apiActionDir/ApiStabilize.php";
		# OldReviewedPages for API
		$classes['ApiQueryOldreviewedpages'] = "$apiReportDir/ApiQueryOldreviewedpages.php";
		# UnreviewedPages for API
		$classes['ApiQueryUnreviewedpages'] = "$apiReportDir/ApiQueryUnreviewedpages.php";
		# ReviewedPages for API
		$classes['ApiQueryReviewedpages'] = "$apiReportDir/ApiQueryReviewedpages.php";
		# ConfiguredPages for API
		$classes['ApiQueryConfiguredpages'] = "$apiReportDir/ApiQueryConfiguredPages.php";
		# Flag metadata for pages for API
		$classes['ApiQueryFlagged'] = "$apiReportDir/ApiQueryFlagged.php";
		# Site flag config for API
		$classes['ApiFlagConfig'] = "$apiReportDir/ApiFlagConfig.php";
		# i18n
		$messagesDirs['FlaggedRevsApi'] = __DIR__ . '/i18n/api';
		### End ###

		### Event handler classes ###
		$classes['FlaggedRevsHooks'] = "$backendDir/FlaggedRevs.hooks.php";
		$classes['FlaggedRevsUIHooks'] = "$frontendDir/FlaggedRevsUI.hooks.php";
		$classes['FlaggedRevsApiHooks'] = "$apiDir/FlaggedRevsApi.hooks.php";
		$classes['FlaggedRevsUpdaterHooks'] = "$schemaDir/FlaggedRevsUpdater.hooks.php";
		$classes['FlaggedRevsTestHooks'] = "$testDir/FlaggedRevsTest.hooks.php";
		### End ###
	}

	/**
	 * Register backend and API hook handlers.
	 * This function must NOT depend on any config vars.
	 *
	 * @return void
	 */
	public static function setUnconditionalHooks() {
		global $wgHooks;

		# ######## API ########
		# Add flagging data to ApiQueryRevisions
		$wgHooks['APIGetAllowedParams'][] = 'FlaggedRevsApiHooks::addApiRevisionParams';
		$wgHooks['APIQueryAfterExecute'][] = 'FlaggedRevsApiHooks::addApiRevisionData';
		# ########

		# ######## Parser #########
		# Parser hooks, selects the desired images/templates
		$wgHooks['BeforeParserFetchTemplateAndtitle'][] = 'FlaggedRevsHooks::parserFetchStableTemplate';
		$wgHooks['BeforeParserFetchFileAndTitle'][] = 'FlaggedRevsHooks::parserFetchStableFile';
		# B/C for before ParserOutput::mImageTimeKeys
		$wgHooks['OutputPageParserOutput'][] = 'FlaggedRevsHooks::outputSetVersioningFlag';
		# ########

		# ######## DB write operations #########
		# Autopromote Editors
		$wgHooks['ArticleSaveComplete'][] = 'FlaggedRevsHooks::onArticleSaveComplete';
		# Auto-reviewing
		$wgHooks['RecentChange_save'][] = 'FlaggedRevsHooks::autoMarkPatrolled';
		$wgHooks['NewRevisionFromEditComplete'][] = 'FlaggedRevsHooks::maybeMakeEditReviewed';
		# Null edit review via checkbox
		$wgHooks['ArticleSaveComplete'][] = 'FlaggedRevsHooks::maybeNullEditReview';
		# User edit tallies
		$wgHooks['ArticleRollbackComplete'][] = 'FlaggedRevsHooks::incrementRollbacks';
		$wgHooks['NewRevisionFromEditComplete'][] = 'FlaggedRevsHooks::incrementReverts';
		# Update fr_page_id and tracking rows on revision restore and merge
		$wgHooks['ArticleRevisionUndeleted'][] = 'FlaggedRevsHooks::onRevisionRestore';
		$wgHooks['ArticleMergeComplete'][] = 'FlaggedRevsHooks::onArticleMergeComplete';

		# Update tracking rows and cache on page changes (@TODO: this sucks):
		# Article edit/create
		$wgHooks['ArticleEditUpdates'][] = 'FlaggedRevsHooks::onArticleEditUpdates';
		# Article delete/restore
		$wgHooks['ArticleDeleteComplete'][] = 'FlaggedRevsHooks::onArticleDelete';
		$wgHooks['ArticleUndelete'][] = 'FlaggedRevsHooks::onArticleUndelete';
		# Revision delete/restore
		$wgHooks['ArticleRevisionVisibilitySet'][] = 'FlaggedRevsHooks::onRevisionDelete';
		# Article move
		$wgHooks['TitleMoveComplete'][] = 'FlaggedRevsHooks::onTitleMoveComplete';
		# File upload
		$wgHooks['FileUpload'][] = 'FlaggedRevsHooks::onFileUpload';
		# ########

		# ######## Other #########
		# Determine what pages can be moved and patrolled
		$wgHooks['getUserPermissionsErrors'][] = 'FlaggedRevsHooks::onGetUserPermissionsErrors';
		# Implicit autoreview rights group
		$wgHooks['AutopromoteCondition'][] = 'FlaggedRevsHooks::checkAutoPromoteCond';
		$wgHooks['UserLoadAfterLoadFromSession'][] = 'FlaggedRevsHooks::setSessionKey';

		# Stable dump hook
		$wgHooks['WikiExporter::dumpStableQuery'][] = 'FlaggedRevsHooks::stableDumpQuery';

		# GNSM category hooks
		$wgHooks['GoogleNewsSitemap::Query'][] = 'FlaggedRevsHooks::gnsmQueryModifier';

		# UserMerge hooks
		$wgHooks['UserMergeAccountFields'][] = 'FlaggedRevsHooks::onUserMergeAccountFields';
		$wgHooks['MergeAccountFromTo'][] = 'FlaggedRevsHooks::onMergeAccountFromTo';
		$wgHooks['DeleteAccount'][] = 'FlaggedRevsHooks::onDeleteAccount';

		# Duplicate flagged* tables in parserTests.php
		$wgHooks['ParserTestTables'][] = 'FlaggedRevsTestHooks::onParserTestTables';
		# Integration tests
		$wgHooks['UnitTestsList'][] = 'FlaggedRevsTestHooks::getUnitTests';

		# Database schema changes
		$wgHooks['LoadExtensionSchemaUpdates'][] = 'FlaggedRevsUpdaterHooks::addSchemaUpdates';
		# ########
	}

	/**
	 * Register FlaggedRevs source code paths.
	 *
	 * @return void
	 */
	public static function setConditionalHooks() {
		global $wgHooks, $wgFlaggedRevsProtection;

		# Give bots the 'autoreview' right (here so it triggers after CentralAuth)
		# @TODO: better way to ensure hook order
		$wgHooks['UserGetRights'][] = 'FlaggedRevsHooks::onUserGetRights';

		if ( $wgFlaggedRevsProtection ) {
			# Add pending changes related magic words
			$wgHooks['ParserFirstCallInit'][] = 'FlaggedRevsHooks::onParserFirstCallInit';
			$wgHooks['ParserGetVariableValueSwitch'][] = 'FlaggedRevsHooks::onParserGetVariableValueSwitch';
			$wgHooks['MagicWordwgVariableIDs'][] = 'FlaggedRevsHooks::onMagicWordwgVariableIDs';
		}

		$wgHooks['EchoGetDefaultNotifiedUsers'][] = 'FlaggedRevsHooks::onEchoGetDefaultNotifiedUsers';

		# ######## User interface #########
		FlaggedRevsUISetup::defineHookHandlers( $wgHooks );
		# ########
	}

	/**
	 * Set $wgAutopromoteOnce
	 *
	 * @return void
	 */
	public static function setAutopromoteConfig() {
		global $wgFlaggedRevsAutoconfirm, $wgFlaggedRevsAutopromote;
		global $wgAutopromoteOnce, $wgGroupPermissions;

		# $wgFlaggedRevsAutoconfirm is now a wrapper around $wgAutopromoteOnce
		$req = $wgFlaggedRevsAutoconfirm; // convenience
		if ( is_array( $req ) ) {
			$criteria = array( '&', // AND
				array( APCOND_AGE, $req['days']*86400 ),
				array( APCOND_EDITCOUNT, $req['edits'], $req['excludeLastDays']*86400 ),
				array( APCOND_FR_EDITSUMMARYCOUNT, $req['editComments'] ),
				array( APCOND_FR_UNIQUEPAGECOUNT, $req['uniqueContentPages'] ),
				array( APCOND_FR_EDITSPACING, $req['spacing'], $req['benchmarks'] ),
				array( '|', // OR
					array( APCOND_FR_CONTENTEDITCOUNT,
						$req['totalContentEdits'], $req['excludeLastDays']*86400 ),
					array( APCOND_FR_CHECKEDEDITCOUNT,
						$req['totalCheckedEdits'], $req['excludeLastDays']*86400 )
				),
			);
			if ( $req['email'] ) {
				$criteria[] = array( APCOND_EMAILCONFIRMED );
			}
			if ( $req['neverBlocked'] ) {
				$criteria[] = array( APCOND_FR_NEVERBOCKED );
			}
			$wgAutopromoteOnce['onEdit']['autoreview'] = $criteria;
			$wgGroupPermissions['autoreview']['autoreview'] = true;
		}

		# $wgFlaggedRevsAutoconfirm is now a wrapper around $wgAutopromoteOnce
		$req = $wgFlaggedRevsAutopromote; // convenience
		if ( is_array( $req ) ) {
			$criteria = array( '&', // AND
				array( APCOND_AGE, $req['days']*86400 ),
				array( APCOND_FR_EDITCOUNT, $req['edits'], $req['excludeLastDays']*86400 ),
				array( APCOND_FR_EDITSUMMARYCOUNT, $req['editComments'] ),
				array( APCOND_FR_UNIQUEPAGECOUNT, $req['uniqueContentPages'] ),
				array( APCOND_FR_USERPAGEBYTES, $req['userpageBytes'] ),
				array( APCOND_FR_NEVERDEMOTED ), // for b/c
				array( APCOND_FR_EDITSPACING, $req['spacing'], $req['benchmarks'] ),
				array( '|', // OR
					array( APCOND_FR_CONTENTEDITCOUNT,
						$req['totalContentEdits'], $req['excludeLastDays']*86400 ),
					array( APCOND_FR_CHECKEDEDITCOUNT,
						$req['totalCheckedEdits'], $req['excludeLastDays']*86400 )
				),
				array( APCOND_FR_MAXREVERTEDEDITRATIO, $req['maxRevertedEditRatio'] ),
				array( '!', APCOND_ISBOT )
			);
			if ( $req['neverBlocked'] ) {
				$criteria[] = array( APCOND_FR_NEVERBOCKED );
			}
			$wgAutopromoteOnce['onEdit']['editor'] = $criteria;
		}
	}

	/**
	 * Set special pages
	 *
	 * @return void
	 */
	public static function setSpecialPages() {
		global $wgSpecialPages, $wgSpecialPageGroups, $wgSpecialPageCacheUpdates;

		FlaggedRevsUISetup::defineSpecialPages(
			$wgSpecialPages, $wgSpecialPageGroups, $wgSpecialPageCacheUpdates );
	}

	/**
	 * Set API modules
	 *
	 * @return void
	 */
	public static function setAPIModules() {
		global $wgAPIModules, $wgAPIListModules, $wgAPIPropModules;
		global $wgFlaggedRevsProtection, $wgAPIGeneratorModules;

		if ( $wgFlaggedRevsProtection ) {
			$wgAPIModules['stabilize'] = 'ApiStabilizeProtect';
		} else {
			$wgAPIModules['stabilize'] = 'ApiStabilizeGeneral';
			$wgAPIListModules['reviewedpages'] = 'ApiQueryReviewedpages';
			$wgAPIListModules['unreviewedpages'] = 'ApiQueryUnreviewedpages';
			$wgAPIListModules['configuredpages'] = 'ApiQueryConfiguredpages';

			$wgAPIGeneratorModules['reviewedpages'] = 'ApiQueryReviewedpages';
			$wgAPIGeneratorModules['unreviewedpages'] = 'ApiQueryUnreviewedpages';
			$wgAPIGeneratorModules['configuredpages'] = 'ApiQueryConfiguredpages';
		}
		# Page review module for API
		$wgAPIModules['review'] = 'ApiReview';
		# Page review activity module for API
		$wgAPIModules['reviewactivity'] = 'ApiReviewActivity';
		# OldReviewedPages for API
		$wgAPIListModules['oldreviewedpages'] = 'ApiQueryOldreviewedpages';
		$wgAPIGeneratorModules['oldreviewedpages'] = 'ApiQueryOldreviewedpages';
		# Flag metadata for pages for API
		$wgAPIPropModules['flagged'] = 'ApiQueryFlagged';
		# Site flag config for API
		$wgAPIModules['flagconfig'] = 'ApiFlagConfig';
	}

	/**
	 * Remove irrelevant user rights
	 *
	 * @return void
	 */
	public static function setConditionalRights() {
		global $wgGroupPermissions, $wgFlaggedRevsProtection;

		if ( $wgFlaggedRevsProtection ) {
			// XXX: Removes sp:ListGroupRights cruft
			if ( isset( $wgGroupPermissions['editor'] ) ) {
				unset( $wgGroupPermissions['editor']['unreviewedpages'] );
			}
			if ( isset( $wgGroupPermissions['reviewer'] ) ) {
				unset( $wgGroupPermissions['reviewer']['unreviewedpages'] );
			}
		}
	}

	/**
	 * Set $wgDefaultUserOptions
	 *
	 * @return void
	 */
	public static function setConditionalPreferences() {
		global $wgDefaultUserOptions, $wgSimpleFlaggedRevsUI;

		$wgDefaultUserOptions['flaggedrevssimpleui'] = (int)$wgSimpleFlaggedRevsUI;
	}
}
