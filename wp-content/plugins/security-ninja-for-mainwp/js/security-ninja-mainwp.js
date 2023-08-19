/* globals jQuery:true, ajaxurl:true, secninja_mainwp:true */
jQuery( document ).ready( function() {
  
  jQuery("#security-ninja").DataTable();
  update_visible_sites();
  

  
  /**
  * Site events switch handler.
  */
  jQuery( 'select[name="security-ninja_length"]' ).on( 'change', function() {
    update_visible_sites();
    return false;
  });
  
  /**
  * Pagination button clicks. 
  */
  jQuery( '#security-ninja_paginate a' ).on( 'click', function() {
    update_visible_sites();
    return false;
  });
  
  /*
  * Hack - clicking any sort table header
  */
  jQuery('th.sorting').on('click', function () {
    update_visible_sites();
    return false;
   });

  /*
  * Hack - clicking pagination button
  */
  jQuery( '.pagination a.paginate_button' ).on( 'click', function () {
    console.log('clicked paginate');
    update_visible_sites();
    return false;
   });
   



  /**
  * update_visible_sites.
  *
  * @author	Lars Koudal
  * @since	v0.0.1
  * @version	v1.0.0	Thursday, May 5th, 2022.	
  * @version	v1.0.1	Wednesday, May 25th, 2022.
  * @global
  * @return	void
  */
  function update_visible_sites() {
    
    // Start main processing
    jQuery('tr.secninrow[data-snload="0"]').each(function () {
      var thistestid = jQuery(this).data('website');


      jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        //async: true,
        data: {
          'action': 'secnin_get_test_info',
          '_ajax_nonce': secninja_mainwp.nonce_secnin_getinfo,
          'website': thistestid
        },
        success: function (data) {
          
          if ( data.data ) {
            jQuery('[data-website="' + thistestid + '"]').attr('data-snload', 1);

            jQuery( '[data-website="' + thistestid + '"] .spinner' ).removeClass('is-active');
            jQuery( '[data-website="' + thistestid + '"] .secnin-score' ).html( data.data.score );
            jQuery( '[data-website="' + thistestid + '"] .secnin-good' ).html( '<span class="ui basic mini compact button green">'+data.data.good+'</span>' );
            jQuery( '[data-website="' + thistestid + '"] .secnin-warning' ).html( '<span class="ui basic mini compact button yellow">'+data.data.warning+'</span>' );   
            jQuery( '[data-website="' + thistestid + '"] .secnin-bad' ).html( '<span class="ui basic mini compact button red">'+data.data.bad+'</span>' );

            // If there are vulns
            if (data.data.vulns !== null) {
              jQuery( '[data-website="' + thistestid + '"] .secnin-vulns' ).html( data.data.vulns ); 
            }
            else {
              jQuery( '[data-website="' + thistestid + '"] .secnin-vulns' ).html( '' ); 
            }
            
            // If there is a secret access URL
            if ( Object.keys(data.data).indexOf('secret_access') !== -1 ) {
              jQuery( '[data-website="' + thistestid + '"] .secnin-secret-access' ).html( '<a href="' + data.data.secret_access + '" target="_blank" rel="noopener"><i class="user secret icon"></i></a>' ); 
            }
            
          }
          else {
            // No results from the website - 
            jQuery( "[data-website='" + thistestid + "'] .spinner" ).removeClass('is-active');
            jQuery( '[data-website="' + thistestid + '"] .secnin-score' ).html( 'N/A' );
          }
        },
        error: function (xhr, textStatus, error) {
          //console.log( 'Website ' + thistestid + ' failed' ); // @debug
        }
      });
    });
    
    
  }
  
  
  
  
  /* Run tests on site */
  /*
  jQuery(document).on('click', '.secnin-action .button.reruntests', function (e) {
    
    jQuery(this).attr('disabled',true);
    
    e.preventDefault();
    var website = jQuery(e).data('website');
    
    jQuery(e).attr('disabled', true);
    jQuery(this).parent('.secnin-action').append('DAVS');
    
    console.log('.reruntests ',secninja_mainwp);
    
    jQuery('.wrap').prepend('<div class="secning-loading-popup"><p>Please wait<span class="spinner is-active"></span></p></div>');
    
    jQuery.ajax({
      url: ajaxurl,
      type: 'POST',
      async: true,
      data: {
        'action' :'secnin_update_site_info',
        '_ajax_nonce': secninja_mainwp.nonce_secnin_getinfo,
        'website': website,
      },
      success: function( response ) {
        console.log( response );
        //window.location.reload();
      },
      error: function( response ) {
        window.location.reload();
      }
    });
    
  });
  */
  
  
  
  
  
  
});