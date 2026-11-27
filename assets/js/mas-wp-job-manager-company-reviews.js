jQuery( function( $ ) {

    $( '.choose-rating' ).on( 'click', '.star', function() {

        // Remove all current stars.
        $( this ).nextAll( '.star' ).removeClass( 'active' );
        $( this ).prevAll( '.star' ).removeClass( 'active' );

        $( this ).addClass( 'active' );

        // Set rating at the hidden input.
        $( this ).parent().find( 'input' ).val( $( this ).attr( 'data-star-rating' ) );
    } );

} );
