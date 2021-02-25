<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\FieldCollection;
use Give\Form\LegacyConsumer\FieldView;

/**
 * Persist custom field values as donation meta.
 *
 * @unreleased
 */
class SetupFieldPersistance implements HookCommandInterface {
	public function __invoke( $hook ) {
		add_action(
			'give_insert_payment',
			function( $donationID, $donationData ) use ( $hook ) {
				$fieldCollection = new FieldCollection( 'root' );
				do_action( "give_fields_$hook", $fieldCollection, $donationData['give_form_id'] );
				$fieldCollection->walk(
					function( $field ) use ( $donationID ) {
						if ( isset( $_POST[ $field->getName() ] ) ) {
							$value = wp_strip_all_tags( $_POST[ $field->getName() ], true );

							if ( $field->shouldStoreAsDonationMeta() ) {
								give_update_payment_meta( $donationID, $field->getName(), $value );
							}

							if ( $field->shouldStoreAsDonorMeta() ) {
								$donorID = give_get_payment_meta( $donationID, '_give_payment_donor_id' );
								Give()->donor_meta->update_meta( $donorID, $field->getName(), $value );
							}
						}
					}
				);
			},
			10,
			2
		);
	}
}

