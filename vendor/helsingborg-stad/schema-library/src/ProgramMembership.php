<?php

namespace Municipio\Schema;

use \Municipio\Schema\Contracts\ProgramMembershipContract;
use \Municipio\Schema\Contracts\IntangibleContract;
use \Municipio\Schema\Contracts\ThingContract;

/**
 * Used to describe membership in a loyalty programs (e.g. "StarAliance"),
 * traveler clubs (e.g. "AAA"), purchase clubs ("Safeway Club"), etc.
 *
 * @see https://schema.org/ProgramMembership
 *
 */
class ProgramMembership extends BaseType implements ProgramMembershipContract, IntangibleContract, ThingContract
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
     * The Organization (airline, travelers' club, retailer, etc.) the
     * membership is made with or which offers the  MemberProgram.
     *
     * @param \Municipio\Schema\Contracts\OrganizationContract|\Municipio\Schema\Contracts\OrganizationContract[] $hostingOrganization
     *
     * @return static
     *
     * @see https://schema.org/hostingOrganization
     */
    public function hostingOrganization($hostingOrganization)
    {
        return $this->setProperty('hostingOrganization', $hostingOrganization);
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
     * A member of an Organization or a ProgramMembership. Organizations can be
     * members of organizations; ProgramMembership is typically for individuals.
     *
     * @param \Municipio\Schema\Contracts\OrganizationContract|\Municipio\Schema\Contracts\OrganizationContract[]|\Municipio\Schema\Contracts\PersonContract|\Municipio\Schema\Contracts\PersonContract[] $member
     *
     * @return static
     *
     * @see https://schema.org/member
     */
    public function member($member)
    {
        return $this->setProperty('member', $member);
    }

    /**
     * A member of this organization.
     *
     * @param \Municipio\Schema\Contracts\OrganizationContract|\Municipio\Schema\Contracts\OrganizationContract[]|\Municipio\Schema\Contracts\PersonContract|\Municipio\Schema\Contracts\PersonContract[] $members
     *
     * @return static
     *
     * @see https://schema.org/members
     */
    public function members($members)
    {
        return $this->setProperty('members', $members);
    }

    /**
     * A unique identifier for the membership.
     *
     * @param string|string[] $membershipNumber
     *
     * @return static
     *
     * @see https://schema.org/membershipNumber
     */
    public function membershipNumber($membershipNumber)
    {
        return $this->setProperty('membershipNumber', $membershipNumber);
    }

    /**
     * The number of membership points earned by the member. If necessary, the
     * unitText can be used to express the units the points are issued in. (E.g.
     * stars, miles, etc.)
     *
     * @param \Municipio\Schema\Contracts\QuantitativeValueContract|\Municipio\Schema\Contracts\QuantitativeValueContract[]|float|float[]|int|int[] $membershipPointsEarned
     *
     * @return static
     *
     * @see https://schema.org/membershipPointsEarned
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/2085
     */
    public function membershipPointsEarned($membershipPointsEarned)
    {
        return $this->setProperty('membershipPointsEarned', $membershipPointsEarned);
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
     * The [MemberProgram](https://schema.org/MemberProgram) associated with a
     * [ProgramMembership](https://schema.org/ProgramMembership).
     *
     * @param \Municipio\Schema\Contracts\MemberProgramContract|\Municipio\Schema\Contracts\MemberProgramContract[] $program
     *
     * @return static
     *
     * @see https://schema.org/program
     * @link https://github.com/schemaorg/schemaorg/issues/3563
     */
    public function program($program)
    {
        return $this->setProperty('program', $program);
    }

    /**
     * The program providing the membership. It is preferable to use
     * [:program](https://schema.org/program) instead.
     *
     * @param string|string[] $programName
     *
     * @return static
     *
     * @see https://schema.org/programName
     */
    public function programName($programName)
    {
        return $this->setProperty('programName', $programName);
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

}
