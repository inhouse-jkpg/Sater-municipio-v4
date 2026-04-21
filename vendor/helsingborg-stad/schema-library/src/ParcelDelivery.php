<?php

namespace Municipio\Schema;

use \Municipio\Schema\Contracts\ParcelDeliveryContract;
use \Municipio\Schema\Contracts\IntangibleContract;
use \Municipio\Schema\Contracts\ThingContract;

/**
 * The delivery of a parcel either via the postal service or a commercial
 * service.
 *
 * @see https://schema.org/ParcelDelivery
 *
 */
class ParcelDelivery extends BaseType implements ParcelDeliveryContract, IntangibleContract, ThingContract
{
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
     * 'carrier' is an out-dated term indicating the 'provider' for parcel
     * delivery and flights.
     *
     * @param \Municipio\Schema\Contracts\OrganizationContract|\Municipio\Schema\Contracts\OrganizationContract[] $carrier
     *
     * @return static
     *
     * @see https://schema.org/carrier
     */
    public function carrier($carrier)
    {
        return $this->setProperty('carrier', $carrier);
    }

    /**
     * Destination address.
     *
     * @param \Municipio\Schema\Contracts\PostalAddressContract|\Municipio\Schema\Contracts\PostalAddressContract[] $deliveryAddress
     *
     * @return static
     *
     * @see https://schema.org/deliveryAddress
     */
    public function deliveryAddress($deliveryAddress)
    {
        return $this->setProperty('deliveryAddress', $deliveryAddress);
    }

    /**
     * New entry added as the package passes through each leg of its journey
     * (from shipment to final delivery).
     *
     * @param \Municipio\Schema\Contracts\DeliveryEventContract|\Municipio\Schema\Contracts\DeliveryEventContract[] $deliveryStatus
     *
     * @return static
     *
     * @see https://schema.org/deliveryStatus
     */
    public function deliveryStatus($deliveryStatus)
    {
        return $this->setProperty('deliveryStatus', $deliveryStatus);
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
     * The earliest date the package may arrive.
     *
     * @param \DateTimeInterface|\DateTimeInterface[] $expectedArrivalFrom
     *
     * @return static
     *
     * @see https://schema.org/expectedArrivalFrom
     */
    public function expectedArrivalFrom($expectedArrivalFrom)
    {
        return $this->setProperty('expectedArrivalFrom', $expectedArrivalFrom);
    }

    /**
     * The latest date the package may arrive.
     *
     * @param \DateTimeInterface|\DateTimeInterface[] $expectedArrivalUntil
     *
     * @return static
     *
     * @see https://schema.org/expectedArrivalUntil
     */
    public function expectedArrivalUntil($expectedArrivalUntil)
    {
        return $this->setProperty('expectedArrivalUntil', $expectedArrivalUntil);
    }

    /**
     * Method used for delivery or shipping.
     *
     * @param \Municipio\Schema\Contracts\DeliveryMethodContract|\Municipio\Schema\Contracts\DeliveryMethodContract[] $hasDeliveryMethod
     *
     * @return static
     *
     * @see https://schema.org/hasDeliveryMethod
     */
    public function hasDeliveryMethod($hasDeliveryMethod)
    {
        return $this->setProperty('hasDeliveryMethod', $hasDeliveryMethod);
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
     * Item(s) being shipped.
     *
     * @param \Municipio\Schema\Contracts\ProductContract|\Municipio\Schema\Contracts\ProductContract[] $itemShipped
     *
     * @return static
     *
     * @see https://schema.org/itemShipped
     */
    public function itemShipped($itemShipped)
    {
        return $this->setProperty('itemShipped', $itemShipped);
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
     * Shipper's address.
     *
     * @param \Municipio\Schema\Contracts\PostalAddressContract|\Municipio\Schema\Contracts\PostalAddressContract[] $originAddress
     *
     * @return static
     *
     * @see https://schema.org/originAddress
     */
    public function originAddress($originAddress)
    {
        return $this->setProperty('originAddress', $originAddress);
    }

    /**
     * The overall order the items in this delivery were included in.
     *
     * @param \Municipio\Schema\Contracts\OrderContract|\Municipio\Schema\Contracts\OrderContract[] $partOfOrder
     *
     * @return static
     *
     * @see https://schema.org/partOfOrder
     */
    public function partOfOrder($partOfOrder)
    {
        return $this->setProperty('partOfOrder', $partOfOrder);
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
     * The service provider, service operator, or service performer; the goods
     * producer. Another party (a seller) may offer those services or goods on
     * behalf of the provider. A provider may also serve as the seller.
     *
     * @param \Municipio\Schema\Contracts\OrganizationContract|\Municipio\Schema\Contracts\OrganizationContract[]|\Municipio\Schema\Contracts\PersonContract|\Municipio\Schema\Contracts\PersonContract[] $provider
     *
     * @return static
     *
     * @see https://schema.org/provider
     * @see https://pending.schema.org
     */
    public function provider($provider)
    {
        return $this->setProperty('provider', $provider);
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
     * Shipper tracking number.
     *
     * @param string|string[] $trackingNumber
     *
     * @return static
     *
     * @see https://schema.org/trackingNumber
     */
    public function trackingNumber($trackingNumber)
    {
        return $this->setProperty('trackingNumber', $trackingNumber);
    }

    /**
     * Tracking url for the parcel delivery.
     *
     * @param string|string[] $trackingUrl
     *
     * @return static
     *
     * @see https://schema.org/trackingUrl
     */
    public function trackingUrl($trackingUrl)
    {
        return $this->setProperty('trackingUrl', $trackingUrl);
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

}
