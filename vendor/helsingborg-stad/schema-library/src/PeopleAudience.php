<?php

namespace Municipio\Schema;

use \Municipio\Schema\Contracts\PeopleAudienceContract;
use \Municipio\Schema\Contracts\AudienceContract;
use \Municipio\Schema\Contracts\IntangibleContract;
use \Municipio\Schema\Contracts\ThingContract;

/**
 * A set of characteristics belonging to people, e.g. who compose an item's
 * target audience.
 *
 * @see https://schema.org/PeopleAudience
 *
 */
class PeopleAudience extends BaseType implements PeopleAudienceContract, AudienceContract, IntangibleContract, ThingContract
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
     * The target group associated with a given audience (e.g. veterans, car
     * owners, musicians, etc.).
     *
     * @param string|string[] $audienceType
     *
     * @return static
     *
     * @see https://schema.org/audienceType
     */
    public function audienceType($audienceType)
    {
        return $this->setProperty('audienceType', $audienceType);
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
     * The geographic area associated with the audience.
     *
     * @param \Municipio\Schema\Contracts\AdministrativeAreaContract|\Municipio\Schema\Contracts\AdministrativeAreaContract[] $geographicArea
     *
     * @return static
     *
     * @see https://schema.org/geographicArea
     */
    public function geographicArea($geographicArea)
    {
        return $this->setProperty('geographicArea', $geographicArea);
    }

    /**
     * Specifying the health condition(s) of a patient, medical study, or other
     * target audience.
     *
     * @param \Municipio\Schema\Contracts\MedicalConditionContract|\Municipio\Schema\Contracts\MedicalConditionContract[] $healthCondition
     *
     * @return static
     *
     * @see https://schema.org/healthCondition
     * @see https://health-lifesci.schema.org
     */
    public function healthCondition($healthCondition)
    {
        return $this->setProperty('healthCondition', $healthCondition);
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
     * Audiences defined by a person's gender.
     *
     * @param string|string[] $requiredGender
     *
     * @return static
     *
     * @see https://schema.org/requiredGender
     */
    public function requiredGender($requiredGender)
    {
        return $this->setProperty('requiredGender', $requiredGender);
    }

    /**
     * Audiences defined by a person's maximum age.
     *
     * @param int|int[] $requiredMaxAge
     *
     * @return static
     *
     * @see https://schema.org/requiredMaxAge
     */
    public function requiredMaxAge($requiredMaxAge)
    {
        return $this->setProperty('requiredMaxAge', $requiredMaxAge);
    }

    /**
     * Audiences defined by a person's minimum age.
     *
     * @param int|int[] $requiredMinAge
     *
     * @return static
     *
     * @see https://schema.org/requiredMinAge
     */
    public function requiredMinAge($requiredMinAge)
    {
        return $this->setProperty('requiredMinAge', $requiredMinAge);
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
     * The age or age range for the intended audience or person, for example
     * 3-12 months for infants, 1-5 years for toddlers.
     *
     * @param \Municipio\Schema\Contracts\QuantitativeValueContract|\Municipio\Schema\Contracts\QuantitativeValueContract[] $suggestedAge
     *
     * @return static
     *
     * @see https://schema.org/suggestedAge
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/2811
     */
    public function suggestedAge($suggestedAge)
    {
        return $this->setProperty('suggestedAge', $suggestedAge);
    }

    /**
     * The suggested gender of the intended person or audience, for example
     * "male", "female", or "unisex".
     *
     * @param \Municipio\Schema\Contracts\GenderTypeContract|\Municipio\Schema\Contracts\GenderTypeContract[]|string|string[] $suggestedGender
     *
     * @return static
     *
     * @see https://schema.org/suggestedGender
     */
    public function suggestedGender($suggestedGender)
    {
        return $this->setProperty('suggestedGender', $suggestedGender);
    }

    /**
     * Maximum recommended age in years for the audience or user.
     *
     * @param float|float[]|int|int[] $suggestedMaxAge
     *
     * @return static
     *
     * @see https://schema.org/suggestedMaxAge
     */
    public function suggestedMaxAge($suggestedMaxAge)
    {
        return $this->setProperty('suggestedMaxAge', $suggestedMaxAge);
    }

    /**
     * A suggested range of body measurements for the intended audience or
     * person, for example inseam between 32 and 34 inches or height between 170
     * and 190 cm. Typically found on a size chart for wearable products.
     *
     * @param \Municipio\Schema\Contracts\QuantitativeValueContract|\Municipio\Schema\Contracts\QuantitativeValueContract[] $suggestedMeasurement
     *
     * @return static
     *
     * @see https://schema.org/suggestedMeasurement
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/2811
     */
    public function suggestedMeasurement($suggestedMeasurement)
    {
        return $this->setProperty('suggestedMeasurement', $suggestedMeasurement);
    }

    /**
     * Minimum recommended age in years for the audience or user.
     *
     * @param float|float[]|int|int[] $suggestedMinAge
     *
     * @return static
     *
     * @see https://schema.org/suggestedMinAge
     */
    public function suggestedMinAge($suggestedMinAge)
    {
        return $this->setProperty('suggestedMinAge', $suggestedMinAge);
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
