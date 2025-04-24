/**
 * Referral Sharing Links.
 *
 * Works with the URLs page to add sharing link functionality.
 *
 * @since 1.0.0
 *
 */

/**
 * Referral Sharing Links handler.
 *
 * Works with the URLs page to add sharing link functionality.
 *
 * @since 1.0.0
 * @global sharingLinks
 *
 * @returns object A sharing links AlpineJS object.
 */
function sharingLinks(){
	return{

		/**
		 * Text.
		 *
		 * Text from settings for Twitter set by twitterInit function.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @type string
		 */
		text: '',

		/**
		 * Subject.
		 *
		 * Subject from settings for email set by emailInit function.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @type string
		 */
		subject: '',

		/**
		 * Body.
		 *
		 * Body from settings for email set by emailInit function.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @type string
		 */
		body: '',

		/**
		 * Twitter Init.
		 *
		 * Adds inline-block to twitter link.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @return {void}
		 */
		twitterInit(){
			document.getElementById("referral-sharing-twitter").parentElement.classList.add("inline-block");
		},

		/**
		 * Twitter Referral Link.
		 *
		 * Creates link and opens window to share the referral link via Twitter.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @fires window.open()
		 *
		 * @return {void}
		 */
		twitterReferralLink(){

			var defaultURL  = "https://twitter.com/intent/tweet?url=";
			var referralURL = AFFWP.portal.core.store.get("urlGeneratorUrls").generated.url;
			var twitterText = "&text=" + encodeURIComponent( this.text );
			var shareLink   = defaultURL + encodeURIComponent( referralURL ) + twitterText;

			window.open(
				shareLink,
				"twitterwindow",
				"left=20,top=20,width=600,height=300,toolbar=0,resizable=1"
			);

			return false;

		},

		/**
		 * Facebook Init.
		 *
		 * Adds inline-block to facebook link.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @return {void}
		 */
		facebookInit(){
			document.getElementById("referral-sharing-facebook").parentElement.classList.add("inline-block");
		},

		/**
		 * Facebook Referral Link.
		 *
		 * Creates link and opens window to share the referral link via Facebook.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @fires window.open()
		 *
		 * @return {void}
		 */
		fbReferralLink(){

			var defaultURL  = "https://www.facebook.com/sharer/sharer.php?u=";
			var referralURL = AFFWP.portal.core.store.get("urlGeneratorUrls").generated.url;
			var shareLink   = defaultURL + encodeURIComponent( referralURL );

			window.open(
				shareLink,
				"facebookwindow",
				"left=20,top=20,width=600,height=700,toolbar=0,resizable=1"
			);

			return false;

		},

		/**
		 * Email Init.
		 *
		 * Adds inline-block to email link.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @return {void}
		 */
		emailInit(){
			document.getElementById("referral-sharing-email").parentElement.classList.add("inline-block");
		},

		/**
		 * Email Referral Link.
		 *
		 * Creates link and opens window to share the referral link via email.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @fires window.open()
		 *
		 * @return {void}
		 */
		emailReferralLink(event){

			// Prevent page reload.
			event.preventDefault();

			var defaultURL   = "mailto:";
			var emailSubject = "?subject=" + encodeURIComponent( this.subject );
			var emailBody    = "&body=" + encodeURIComponent( this.body ) + " ";
			var referralURL  = AFFWP.portal.core.store.get("urlGeneratorUrls").generated.url;
			var shareLink    = document.getElementById("referral-sharing-email");
			var link         = defaultURL + emailSubject + emailBody + encodeURIComponent( referralURL );

			// currently only works if you have an email handler setup
			window.open( link, '_self' );

			// secondary option to work on Chrome if you don't have an email handler setup
			shareLink.href = link;
			shareLink.click();
			shareLink.href = '';

		},

	}
}
export default sharingLinks;