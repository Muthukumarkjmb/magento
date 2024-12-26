<?php

namespace Burstonline\Feeconfig\Model;

class FeeconfigFinish
{
	const NONE = 0;
	
	public function getFeeType()
	{
		return [
			self::NONE => ' - ',
			'percentage' => 'Percentage',
			'flat' => 'Flat'
		];
	}
	public function getApplicationMethod()
	{
		return [
			self::NONE => ' - ',
			'applied_per_item' => 'Applied per Item',
			'applied_to_cart' => 'Applied to Order'
		];
	}
	public function getAppliesTo()
	{
		return [
			self::NONE => ' - ',
			'delivery' => 'Delivery',
			'pickup' => 'Pickup',
			'both' => 'Both'
		];
	}
	public function getMapping()
	{
		return [
			self::NONE => ' ',
			'serviceFee' => 'Service Fee',
			'deliveryFee' => 'Delivery Fee',
			'convenienceFee' => 'Convenience Fee'
		];
	}
}
