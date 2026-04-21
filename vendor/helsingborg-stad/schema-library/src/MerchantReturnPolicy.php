<?php

namespace Municipio\Schema;

use \Municipio\Schema\Contracts\MerchantReturnPolicyContract;
use \Municipio\Schema\Contracts\IntangibleContract;
use \Municipio\Schema\Contracts\ThingContract;

/**
 * A MerchantReturnPolicy provides information about product return policies
 * associated with an [[Organization]], [[Product]], or [[Offer]].
 *
 * @see https://schema.org/MerchantReturnPolicy
 * @see https://pending.schema.org
 * @link https://github.com/schemaorg/schemaorg/issues/2288
 *
 */
class MerchantReturnPolicy extends BaseType implements MerchantReturnPolicyContract, IntangibleContract, ThingContract
{
    /**
     * A property-value pair representing an additional characteristic of the
     * entity, e.g. a product feature or another characteristic for which there
     * is no matching property in schema.org.
     * 
     * Note: Publishers should be aware that applications designed to use
     * specific schema.org properties (e.g. https://schema.org/width,
     * https://schema.org/color, https://schema.org/gtin13, ...) will typically
     * expect such data to be provided using those properties, rather than using
     * the generic property/value mechanism.
     *
     * @param \Municipio\Schema\Contracts\PropertyValueContract|\Municipio\Schema\Contracts\PropertyValueContract[] $additionalProperty
     *
     * @return static
     *
     * @see https://schema.org/additionalProperty
     */
    public function additionalProperty($additionalProperty)
    {
        return $this->setProperty('additionalProperty', $additionalProperty);
    }

    /**
     * An additional type for the item, typically used for adding more specific
     * types from external vocabularies in microdata syntax. This is a
     * relationship between something and a class that the thing is in.
     * Typically the value is a URI-identified RDF class, and in this case
     * corresponds to the
     *     use of rdf:type in RDF. Text values can be used sparingly, for cases
     * where useful information can be added without their being an appropriate
     * schema to reference. In the case of text values, the class label should
     * follow the schema.org [style
     * guide](https://schema.org/docs/styleguide.html).
     *
     * @param string|string[] $additionalType
     *
     * @return static
     *
     * @see https://schema.org/additionalType
     */
    public function additionalType($additionalType)
    {
        return $this->setProperty('additionalType', $additionalType);
    }

    /**
     * An alias for the item.
     *
     * @param string|string[] $alternateName
     *
     * @return static
     *
     * @see https://schema.org/alternateName
     */
    public function alternateName($alternateName)
    {
        return $this->setProperty('alternateName', $alternateName);
    }

    /**
     * A country where a particular merchant return policy applies to, for
     * example the two-letter ISO 3166-1 alpha-2 country code.
     *
     * @param \Municipio\Schema\Contracts\CountryContract|\Municipio\Schema\Contracts\CountryContract[]|string|string[] $applicableCountry
     *
     * @return static
     *
     * @see https://schema.org/applicableCountry
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/3001
     */
    public function applicableCountry($applicableCountry)
    {
        return $this->setProperty('applicableCountry', $applicableCountry);
    }

    /**
     * The type of return fees if the product is returned due to customer
     * remorse.
     *
     * @param \Municipio\Schema\Contracts\ReturnFeesEnumerationContract|\Municipio\Schema\Contracts\ReturnFeesEnumerationContract[] $customerRemorseReturnFees
     *
     * @return static
     *
     * @see https://schema.org/customerRemorseReturnFees
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/2880
     */
    public function customerRemorseReturnFees($customerRemorseReturnFees)
    {
        return $this->setProperty('customerRemorseReturnFees', $customerRemorseReturnFees);
    }

    /**
     * The method (from an enumeration) by which the customer obtains a return
     * shipping label for a product returned due to customer remorse.
     *
     * @param \Municipio\Schema\Contracts\ReturnLabelSourceEnumerationContract|\Municipio\Schema\Contracts\ReturnLabelSourceEnumerationContract[] $customerRemorseReturnLabelSource
     *
     * @return static
     *
     * @see https://schema.org/customerRemorseReturnLabelSource
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/2880
     */
    public function customerRemorseReturnLabelSource($customerRemorseReturnLabelSource)
    {
        return $this->setProperty('customerRemorseReturnLabelSource', $customerRemorseReturnLabelSource);
    }

    /**
     * The amount of shipping costs if a product is returned due to customer
     * remorse. Applicable when property [[customerRemorseReturnFees]] equals
     * [[ReturnShippingFees]].
     *
     * @param \Municipio\Schema\Contracts\MonetaryAmountContract|\Municipio\Schema\Contracts\MonetaryAmountContract[] $customerRemorseReturnShippingFeesAmount
     *
     * @return static
     *
     * @see https://schema.org/customerRemorseReturnShippingFeesAmount
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/2880
     */
    public function customerRemorseReturnShippingFeesAmount($customerRemorseReturnShippingFeesAmount)
    {
        return $this->setProperty('customerRemorseReturnShippingFeesAmount', $customerRemorseReturnShippingFeesAmount);
    }

    /**
     * A description of the item.
     *
     * @param \Municipio\Schema\Contracts\TextObjectContract|\Municipio\Schema\Contracts\TextObjectContract[]|string|string[] $description
     *
     * @return static
     *
     * @see https://schema.org/description
     */
    public function description($description)
    {
        return $this->setProperty('description', $description);
    }

    /**
     * A sub property of description. A short description of the item used to
     * disambiguate from other, similar items. Information from other properties
     * (in particular, name) may be necessary for the description to be useful
     * for disambiguation.
     *
     * @param string|string[] $disambiguatingDescription
     *
     * @return static
     *
     * @see https://schema.org/disambiguatingDescription
     */
    public function disambiguatingDescription($disambiguatingDescription)
    {
        return $this->setProperty('disambiguatingDescription', $disambiguatingDescription);
    }

    /**
     * The identifier property represents any kind of identifier for any kind of
     * [[Thing]], such as ISBNs, GTIN codes, UUIDs etc. Schema.org provides
     * dedicated properties for representing many of these, either as textual
     * strings or as URL (URI) links. See [background
     * notes](/docs/datamodel.html#identifierBg) for more details.
     *
     * @param \Municipio\Schema\Contracts\PropertyValueContract|\Municipio\Schema\Contracts\PropertyValueContract[]|string|string[] $identifier
     *
     * @return static
     *
     * @see https://schema.org/identifier
     */
    public function identifier($identifier)
    {
        return $this->setProperty('identifier', $identifier);
    }

    /**
     * An image of the item. This can be a [[URL]] or a fully described
     * [[ImageObject]].
     *
     * @param \Municipio\Schema\Contracts\ImageObjectContract|\Municipio\Schema\Contracts\ImageObjectContract[]|string|string[] $image
     *
     * @return static
     *
     * @see https://schema.org/image
     */
    public function image($image)
    {
        return $this->setProperty('image', $image);
    }

    /**
     * Are in-store returns offered? (For more advanced return methods use the
     * [[returnMethod]] property.)
     *
     * @param bool|bool[] $inStoreReturnsOffered
     *
     * @return static
     *
     * @see https://schema.org/inStoreReturnsOffered
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/2288
     */
    public function inStoreReturnsOffered($inStoreReturnsOffered)
    {
        return $this->setProperty('inStoreReturnsOffered', $inStoreReturnsOffered);
    }

    /**
     * A predefined value from OfferItemCondition specifying the condition of
     * the product or service, or the products or services included in the
     * offer. Also used for product return policies to specify the condition of
     * products accepted for returns.
     *
     * @param \Municipio\Schema\Contracts\OfferItemConditionContract|\Municipio\Schema\Contracts\OfferItemConditionContract[] $itemCondition
     *
     * @return static
     *
     * @see https://schema.org/itemCondition
     */
    public function itemCondition($itemCondition)
    {
        return $this->setProperty('itemCondition', $itemCondition);
    }

    /**
     * The type of return fees for returns of defect products.
     *
     * @param \Municipio\Schema\Contracts\ReturnFeesEnumerationContract|\Municipio\Schema\Contracts\ReturnFeesEnumerationContract[] $itemDefectReturnFees
     *
     * @return static
     *
     * @see https://schema.org/itemDefectReturnFees
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/2880
     */
    public function itemDefectReturnFees($itemDefectReturnFees)
    {
        return $this->setProperty('itemDefectReturnFees', $itemDefectReturnFees);
    }

    /**
     * The method (from an enumeration) by which the customer obtains a return
     * shipping label for a defect product.
     *
     * @param \Municipio\Schema\Contracts\ReturnLabelSourceEnumerationContract|\Municipio\Schema\Contracts\ReturnLabelSourceEnumerationContract[] $itemDefectReturnLabelSource
     *
     * @return static
     *
     * @see https://schema.org/itemDefectReturnLabelSource
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/2880
     */
    public function itemDefectReturnLabelSource($itemDefectReturnLabelSource)
    {
        return $this->setProperty('itemDefectReturnLabelSource', $itemDefectReturnLabelSource);
    }

    /**
     * Amount of shipping costs for defect product returns. Applicable when
     * property [[itemDefectReturnFees]] equals [[ReturnShippingFees]].
     *
     * @param \Municipio\Schema\Contracts\MonetaryAmountContract|\Municipio\Schema\Contracts\MonetaryAmountContract[] $itemDefectReturnShippingFeesAmount
     *
     * @return static
     *
     * @see https://schema.org/itemDefectReturnShippingFeesAmount
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/2880
     */
    public function itemDefectReturnShippingFeesAmount($itemDefectReturnShippingFeesAmount)
    {
        return $this->setProperty('itemDefectReturnShippingFeesAmount', $itemDefectReturnShippingFeesAmount);
    }

    /**
     * Indicates a page (or other CreativeWork) for which this thing is the main
     * entity being described. See [background
     * notes](/docs/datamodel.html#mainEntityBackground) for details.
     *
     * @param \Municipio\Schema\Contracts\CreativeWorkContract|\Municipio\Schema\Contracts\CreativeWorkContract[]|string|string[] $mainEntityOfPage
     *
     * @return static
     *
     * @see https://schema.org/mainEntityOfPage
     */
    public function mainEntityOfPage($mainEntityOfPage)
    {
        return $this->setProperty('mainEntityOfPage', $mainEntityOfPage);
    }

    /**
     * Specifies either a fixed return date or the number of days (from the
     * delivery date) that a product can be returned. Used when the
     * [[returnPolicyCategory]] property is specified as
     * [[MerchantReturnFiniteReturnWindow]].
     *
     * @param \DateTimeInterface|\DateTimeInterface[]|int|int[] $merchantReturnDays
     *
     * @return static
     *
     * @see https://schema.org/merchantReturnDays
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/2288
     */
    public function merchantReturnDays($merchantReturnDays)
    {
        return $this->setProperty('merchantReturnDays', $merchantReturnDays);
    }

    /**
     * Specifies a Web page or service by URL, for product returns.
     *
     * @param string|string[] $merchantReturnLink
     *
     * @return static
     *
     * @see https://schema.org/merchantReturnLink
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/2288
     */
    public function merchantReturnLink($merchantReturnLink)
    {
        return $this->setProperty('merchantReturnLink', $merchantReturnLink);
    }

    /**
     * The name of the item.
     *
     * @param string|string[] $name
     *
     * @return static
     *
     * @see https://schema.org/name
     */
    public function name($name)
    {
        return $this->setProperty('name', $name);
    }

    /**
     * Indicates a potential Action, which describes an idealized action in
     * which this thing would play an 'object' role.
     *
     * @param \Municipio\Schema\Contracts\ActionContract|\Municipio\Schema\Contracts\ActionContract[] $potentialAction
     *
     * @return static
     *
     * @see https://schema.org/potentialAction
     */
    public function potentialAction($potentialAction)
    {
        return $this->setProperty('potentialAction', $potentialAction);
    }

    /**
     * A refund type, from an enumerated list.
     *
     * @param \Municipio\Schema\Contracts\RefundTypeEnumerationContract|\Municipio\Schema\Contracts\RefundTypeEnumerationContract[] $refundType
     *
     * @return static
     *
     * @see https://schema.org/refundType
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/2288
     */
    public function refundType($refundType)
    {
        return $this->setProperty('refundType', $refundType);
    }

    /**
     * Use [[MonetaryAmount]] to specify a fixed restocking fee for product
     * returns, or use [[Number]] to specify a percentage of the product price
     * paid by the customer.
     *
     * @param \Municipio\Schema\Contracts\MonetaryAmountContract|\Municipio\Schema\Contracts\MonetaryAmountContract[]|float|float[]|int|int[] $restockingFee
     *
     * @return static
     *
     * @see https://schema.org/restockingFee
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/2880
     */
    public function restockingFee($restockingFee)
    {
        return $this->setProperty('restockingFee', $restockingFee);
    }

    /**
     * The type of return fees for purchased products (for any return reason).
     *
     * @param \Municipio\Schema\Contracts\ReturnFeesEnumerationContract|\Municipio\Schema\Contracts\ReturnFeesEnumerationContract[] $returnFees
     *
     * @return static
     *
     * @see https://schema.org/returnFees
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/2288
     */
    public function returnFees($returnFees)
    {
        return $this->setProperty('returnFees', $returnFees);
    }

    /**
     * The method (from an enumeration) by which the customer obtains a return
     * shipping label for a product returned for any reason.
     *
     * @param \Municipio\Schema\Contracts\ReturnLabelSourceEnumerationContract|\Municipio\Schema\Contracts\ReturnLabelSourceEnumerationContract[] $returnLabelSource
     *
     * @return static
     *
     * @see https://schema.org/returnLabelSource
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/2880
     */
    public function returnLabelSource($returnLabelSource)
    {
        return $this->setProperty('returnLabelSource', $returnLabelSource);
    }

    /**
     * The type of return method offered, specified from an enumeration.
     *
     * @param \Municipio\Schema\Contracts\ReturnMethodEnumerationContract|\Municipio\Schema\Contracts\ReturnMethodEnumerationContract[] $returnMethod
     *
     * @return static
     *
     * @see https://schema.org/returnMethod
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/2880
     */
    public function returnMethod($returnMethod)
    {
        return $this->setProperty('returnMethod', $returnMethod);
    }

    /**
     * Specifies an applicable return policy (from an enumeration).
     *
     * @param \Municipio\Schema\Contracts\MerchantReturnEnumerationContract|\Municipio\Schema\Contracts\MerchantReturnEnumerationContract[] $returnPolicyCategory
     *
     * @return static
     *
     * @see https://schema.org/returnPolicyCategory
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/2288
     */
    public function returnPolicyCategory($returnPolicyCategory)
    {
        return $this->setProperty('returnPolicyCategory', $returnPolicyCategory);
    }

    /**
     * The country where the product has to be sent to for returns, for example
     * "Ireland" using the [[name]] property of [[Country]]. You can also
     * provide the two-letter [ISO 3166-1 alpha-2 country
     * code](http://en.wikipedia.org/wiki/ISO_3166-1). Note that this can be
     * different from the country where the product was originally shipped from
     * or sent to.
     *
     * @param \Municipio\Schema\Contracts\CountryContract|\Municipio\Schema\Contracts\CountryContract[]|string|string[] $returnPolicyCountry
     *
     * @return static
     *
     * @see https://schema.org/returnPolicyCountry
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/2880
     */
    public function returnPolicyCountry($returnPolicyCountry)
    {
        return $this->setProperty('returnPolicyCountry', $returnPolicyCountry);
    }

    /**
     * Seasonal override of a return policy.
     *
     * @param \Municipio\Schema\Contracts\MerchantReturnPolicySeasonalOverrideContract|\Municipio\Schema\Contracts\MerchantReturnPolicySeasonalOverrideContract[] $returnPolicySeasonalOverride
     *
     * @return static
     *
     * @see https://schema.org/returnPolicySeasonalOverride
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/2880
     */
    public function returnPolicySeasonalOverride($returnPolicySeasonalOverride)
    {
        return $this->setProperty('returnPolicySeasonalOverride', $returnPolicySeasonalOverride);
    }

    /**
     * Amount of shipping costs for product returns (for any reason). Applicable
     * when property [[returnFees]] equals [[ReturnShippingFees]].
     *
     * @param \Municipio\Schema\Contracts\MonetaryAmountContract|\Municipio\Schema\Contracts\MonetaryAmountContract[] $returnShippingFeesAmount
     *
     * @return static
     *
     * @see https://schema.org/returnShippingFeesAmount
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/2880
     */
    public function returnShippingFeesAmount($returnShippingFeesAmount)
    {
        return $this->setProperty('returnShippingFeesAmount', $returnShippingFeesAmount);
    }

    /**
     * URL of a reference Web page that unambiguously indicates the item's
     * identity. E.g. the URL of the item's Wikipedia page, Wikidata entry, or
     * official website.
     *
     * @param string|string[] $sameAs
     *
     * @return static
     *
     * @see https://schema.org/sameAs
     */
    public function sameAs($sameAs)
    {
        return $this->setProperty('sameAs', $sameAs);
    }

    /**
     * A CreativeWork or Event about this Thing.
     *
     * @param \Municipio\Schema\Contracts\CreativeWorkContract|\Municipio\Schema\Contracts\CreativeWorkContract[]|\Municipio\Schema\Contracts\EventContract|\Municipio\Schema\Contracts\EventContract[] $subjectOf
     *
     * @return static
     *
     * @see https://schema.org/subjectOf
     * @link https://github.com/schemaorg/schemaorg/issues/1670
     */
    public function subjectOf($subjectOf)
    {
        return $this->setProperty('subjectOf', $subjectOf);
    }

    /**
     * URL of the item.
     *
     * @param string|string[] $url
     *
     * @return static
     *
     * @see https://schema.org/url
     */
    public function url($url)
    {
        return $this->setProperty('url', $url);
    }

    /**
     * The membership program tier an Offer (or a PriceSpecification,
     * OfferShippingDetails, or MerchantReturnPolicy under an Offer) is valid
     * for.
     *
     * @param \Municipio\Schema\Contracts\MemberProgramTierContract|\Municipio\Schema\Contracts\MemberProgramTierContract[] $validForMemberTier
     *
     * @return static
     *
     * @see https://schema.org/validForMemberTier
     * @link https://github.com/schemaorg/schemaorg/issues/3563
     */
    public function validForMemberTier($validForMemberTier)
    {
        return $this->setProperty('validForMemberTier', $validForMemberTier);
    }

}
