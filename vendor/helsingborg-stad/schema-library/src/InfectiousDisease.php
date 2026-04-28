<?php

namespace Municipio\Schema;

use \Municipio\Schema\Contracts\InfectiousDiseaseContract;
use \Municipio\Schema\Contracts\MedicalConditionContract;
use \Municipio\Schema\Contracts\MedicalEntityContract;
use \Municipio\Schema\Contracts\ThingContract;

/**
 * An infectious disease is a clinically evident human disease resulting from
 * the presence of pathogenic microbial agents, like pathogenic viruses,
 * pathogenic bacteria, fungi, protozoa, multicellular parasites, and prions. To
 * be considered an infectious disease, such pathogens are known to be able to
 * cause this disease.
 *
 * @see https://schema.org/InfectiousDisease
 * @see https://health-lifesci.schema.org
 *
 */
class InfectiousDisease extends BaseType implements InfectiousDiseaseContract, MedicalConditionContract, MedicalEntityContract, ThingContract
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
     * The anatomy of the underlying organ system or structures associated with
     * this entity.
     *
     * @param \Municipio\Schema\Contracts\AnatomicalStructureContract|\Municipio\Schema\Contracts\AnatomicalStructureContract[]|\Municipio\Schema\Contracts\AnatomicalSystemContract|\Municipio\Schema\Contracts\AnatomicalSystemContract[]|\Municipio\Schema\Contracts\SuperficialAnatomyContract|\Municipio\Schema\Contracts\SuperficialAnatomyContract[] $associatedAnatomy
     *
     * @return static
     *
     * @see https://schema.org/associatedAnatomy
     * @see https://health-lifesci.schema.org
     */
    public function associatedAnatomy($associatedAnatomy)
    {
        return $this->setProperty('associatedAnatomy', $associatedAnatomy);
    }

    /**
     * A medical code for the entity, taken from a controlled vocabulary or
     * ontology such as ICD-9, DiseasesDB, MeSH, SNOMED-CT, RxNorm, etc.
     *
     * @param \Municipio\Schema\Contracts\MedicalCodeContract|\Municipio\Schema\Contracts\MedicalCodeContract[] $code
     *
     * @return static
     *
     * @see https://schema.org/code
     * @see https://health-lifesci.schema.org
     */
    public function code($code)
    {
        return $this->setProperty('code', $code);
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
     * One of a set of differential diagnoses for the condition. Specifically, a
     * closely-related or competing diagnosis typically considered later in the
     * cognitive process whereby this medical condition is distinguished from
     * others most likely responsible for a similar collection of signs and
     * symptoms to reach the most parsimonious diagnosis or diagnoses in a
     * patient.
     *
     * @param \Municipio\Schema\Contracts\DDxElementContract|\Municipio\Schema\Contracts\DDxElementContract[] $differentialDiagnosis
     *
     * @return static
     *
     * @see https://schema.org/differentialDiagnosis
     * @see https://health-lifesci.schema.org
     */
    public function differentialDiagnosis($differentialDiagnosis)
    {
        return $this->setProperty('differentialDiagnosis', $differentialDiagnosis);
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
     * Specifying a drug or medicine used in a medication procedure.
     *
     * @param \Municipio\Schema\Contracts\DrugContract|\Municipio\Schema\Contracts\DrugContract[] $drug
     *
     * @return static
     *
     * @see https://schema.org/drug
     * @see https://health-lifesci.schema.org
     */
    public function drug($drug)
    {
        return $this->setProperty('drug', $drug);
    }

    /**
     * The characteristics of associated patients, such as age, gender, race
     * etc.
     *
     * @param string|string[] $epidemiology
     *
     * @return static
     *
     * @see https://schema.org/epidemiology
     * @see https://health-lifesci.schema.org
     */
    public function epidemiology($epidemiology)
    {
        return $this->setProperty('epidemiology', $epidemiology);
    }

    /**
     * The likely outcome in either the short term or long term of the medical
     * condition.
     *
     * @param string|string[] $expectedPrognosis
     *
     * @return static
     *
     * @see https://schema.org/expectedPrognosis
     * @see https://health-lifesci.schema.org
     */
    public function expectedPrognosis($expectedPrognosis)
    {
        return $this->setProperty('expectedPrognosis', $expectedPrognosis);
    }

    /**
     * A [[Grant]] that directly or indirectly provide funding or sponsorship
     * for this item. See also [[ownershipFundingInfo]].
     *
     * @param \Municipio\Schema\Contracts\GrantContract|\Municipio\Schema\Contracts\GrantContract[] $funding
     *
     * @return static
     *
     * @see https://schema.org/funding
     * @see https://pending.schema.org
     * @link https://github.com/schemaorg/schemaorg/issues/383
     */
    public function funding($funding)
    {
        return $this->setProperty('funding', $funding);
    }

    /**
     * A medical guideline related to this entity.
     *
     * @param \Municipio\Schema\Contracts\MedicalGuidelineContract|\Municipio\Schema\Contracts\MedicalGuidelineContract[] $guideline
     *
     * @return static
     *
     * @see https://schema.org/guideline
     * @see https://health-lifesci.schema.org
     */
    public function guideline($guideline)
    {
        return $this->setProperty('guideline', $guideline);
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
     * The actual infectious agent, such as a specific bacterium.
     *
     * @param string|string[] $infectiousAgent
     *
     * @return static
     *
     * @see https://schema.org/infectiousAgent
     * @see https://health-lifesci.schema.org
     */
    public function infectiousAgent($infectiousAgent)
    {
        return $this->setProperty('infectiousAgent', $infectiousAgent);
    }

    /**
     * The class of infectious agent (bacteria, prion, etc.) that causes the
     * disease.
     *
     * @param \Municipio\Schema\Contracts\InfectiousAgentClassContract|\Municipio\Schema\Contracts\InfectiousAgentClassContract[] $infectiousAgentClass
     *
     * @return static
     *
     * @see https://schema.org/infectiousAgentClass
     * @see https://health-lifesci.schema.org
     */
    public function infectiousAgentClass($infectiousAgentClass)
    {
        return $this->setProperty('infectiousAgentClass', $infectiousAgentClass);
    }

    /**
     * The drug or supplement's legal status, including any controlled substance
     * schedules that apply.
     *
     * @param \Municipio\Schema\Contracts\DrugLegalStatusContract|\Municipio\Schema\Contracts\DrugLegalStatusContract[]|\Municipio\Schema\Contracts\MedicalEnumerationContract|\Municipio\Schema\Contracts\MedicalEnumerationContract[]|string|string[] $legalStatus
     *
     * @return static
     *
     * @see https://schema.org/legalStatus
     * @see https://health-lifesci.schema.org
     */
    public function legalStatus($legalStatus)
    {
        return $this->setProperty('legalStatus', $legalStatus);
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
     * The system of medicine that includes this MedicalEntity, for example
     * 'evidence-based', 'homeopathic', 'chiropractic', etc.
     *
     * @param \Municipio\Schema\Contracts\MedicineSystemContract|\Municipio\Schema\Contracts\MedicineSystemContract[] $medicineSystem
     *
     * @return static
     *
     * @see https://schema.org/medicineSystem
     * @see https://health-lifesci.schema.org
     */
    public function medicineSystem($medicineSystem)
    {
        return $this->setProperty('medicineSystem', $medicineSystem);
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
     * The expected progression of the condition if it is not treated and
     * allowed to progress naturally.
     *
     * @param string|string[] $naturalProgression
     *
     * @return static
     *
     * @see https://schema.org/naturalProgression
     * @see https://health-lifesci.schema.org
     */
    public function naturalProgression($naturalProgression)
    {
        return $this->setProperty('naturalProgression', $naturalProgression);
    }

    /**
     * Changes in the normal mechanical, physical, and biochemical functions
     * that are associated with this activity or condition.
     *
     * @param string|string[] $pathophysiology
     *
     * @return static
     *
     * @see https://schema.org/pathophysiology
     * @see https://health-lifesci.schema.org
     */
    public function pathophysiology($pathophysiology)
    {
        return $this->setProperty('pathophysiology', $pathophysiology);
    }

    /**
     * A possible unexpected and unfavorable evolution of a medical condition.
     * Complications may include worsening of the signs or symptoms of the
     * disease, extension of the condition to other organ systems, etc.
     *
     * @param string|string[] $possibleComplication
     *
     * @return static
     *
     * @see https://schema.org/possibleComplication
     * @see https://health-lifesci.schema.org
     */
    public function possibleComplication($possibleComplication)
    {
        return $this->setProperty('possibleComplication', $possibleComplication);
    }

    /**
     * A possible treatment to address this condition, sign or symptom.
     *
     * @param \Municipio\Schema\Contracts\MedicalTherapyContract|\Municipio\Schema\Contracts\MedicalTherapyContract[] $possibleTreatment
     *
     * @return static
     *
     * @see https://schema.org/possibleTreatment
     * @see https://health-lifesci.schema.org
     */
    public function possibleTreatment($possibleTreatment)
    {
        return $this->setProperty('possibleTreatment', $possibleTreatment);
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
     * A preventative therapy used to prevent an initial occurrence of the
     * medical condition, such as vaccination.
     *
     * @param \Municipio\Schema\Contracts\MedicalTherapyContract|\Municipio\Schema\Contracts\MedicalTherapyContract[] $primaryPrevention
     *
     * @return static
     *
     * @see https://schema.org/primaryPrevention
     * @see https://health-lifesci.schema.org
     */
    public function primaryPrevention($primaryPrevention)
    {
        return $this->setProperty('primaryPrevention', $primaryPrevention);
    }

    /**
     * If applicable, the organization that officially recognizes this entity as
     * part of its endorsed system of medicine.
     *
     * @param \Municipio\Schema\Contracts\OrganizationContract|\Municipio\Schema\Contracts\OrganizationContract[] $recognizingAuthority
     *
     * @return static
     *
     * @see https://schema.org/recognizingAuthority
     * @see https://health-lifesci.schema.org
     */
    public function recognizingAuthority($recognizingAuthority)
    {
        return $this->setProperty('recognizingAuthority', $recognizingAuthority);
    }

    /**
     * If applicable, a medical specialty in which this entity is relevant.
     *
     * @param \Municipio\Schema\Contracts\MedicalSpecialtyContract|\Municipio\Schema\Contracts\MedicalSpecialtyContract[] $relevantSpecialty
     *
     * @return static
     *
     * @see https://schema.org/relevantSpecialty
     * @see https://health-lifesci.schema.org
     */
    public function relevantSpecialty($relevantSpecialty)
    {
        return $this->setProperty('relevantSpecialty', $relevantSpecialty);
    }

    /**
     * A modifiable or non-modifiable factor that increases the risk of a
     * patient contracting this condition, e.g. age,  coexisting condition.
     *
     * @param \Municipio\Schema\Contracts\MedicalRiskFactorContract|\Municipio\Schema\Contracts\MedicalRiskFactorContract[] $riskFactor
     *
     * @return static
     *
     * @see https://schema.org/riskFactor
     * @see https://health-lifesci.schema.org
     */
    public function riskFactor($riskFactor)
    {
        return $this->setProperty('riskFactor', $riskFactor);
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
     * A preventative therapy used to prevent reoccurrence of the medical
     * condition after an initial episode of the condition.
     *
     * @param \Municipio\Schema\Contracts\MedicalTherapyContract|\Municipio\Schema\Contracts\MedicalTherapyContract[] $secondaryPrevention
     *
     * @return static
     *
     * @see https://schema.org/secondaryPrevention
     * @see https://health-lifesci.schema.org
     */
    public function secondaryPrevention($secondaryPrevention)
    {
        return $this->setProperty('secondaryPrevention', $secondaryPrevention);
    }

    /**
     * A sign or symptom of this condition. Signs are objective or physically
     * observable manifestations of the medical condition while symptoms are the
     * subjective experience of the medical condition.
     *
     * @param \Municipio\Schema\Contracts\MedicalSignOrSymptomContract|\Municipio\Schema\Contracts\MedicalSignOrSymptomContract[] $signOrSymptom
     *
     * @return static
     *
     * @see https://schema.org/signOrSymptom
     * @see https://health-lifesci.schema.org
     */
    public function signOrSymptom($signOrSymptom)
    {
        return $this->setProperty('signOrSymptom', $signOrSymptom);
    }

    /**
     * The stage of the condition, if applicable.
     *
     * @param \Municipio\Schema\Contracts\MedicalConditionStageContract|\Municipio\Schema\Contracts\MedicalConditionStageContract[] $stage
     *
     * @return static
     *
     * @see https://schema.org/stage
     * @see https://health-lifesci.schema.org
     */
    public function stage($stage)
    {
        return $this->setProperty('stage', $stage);
    }

    /**
     * The status of the study (enumerated).
     *
     * @param \Municipio\Schema\Contracts\EventStatusTypeContract|\Municipio\Schema\Contracts\EventStatusTypeContract[]|\Municipio\Schema\Contracts\MedicalStudyStatusContract|\Municipio\Schema\Contracts\MedicalStudyStatusContract[]|string|string[] $status
     *
     * @return static
     *
     * @see https://schema.org/status
     * @see https://health-lifesci.schema.org
     */
    public function status($status)
    {
        return $this->setProperty('status', $status);
    }

    /**
     * A medical study or trial related to this entity.
     *
     * @param \Municipio\Schema\Contracts\MedicalStudyContract|\Municipio\Schema\Contracts\MedicalStudyContract[] $study
     *
     * @return static
     *
     * @see https://schema.org/study
     * @see https://health-lifesci.schema.org
     */
    public function study($study)
    {
        return $this->setProperty('study', $study);
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
     * How the disease spreads, either as a route or vector, for example 'direct
     * contact', 'Aedes aegypti', etc.
     *
     * @param string|string[] $transmissionMethod
     *
     * @return static
     *
     * @see https://schema.org/transmissionMethod
     * @see https://health-lifesci.schema.org
     */
    public function transmissionMethod($transmissionMethod)
    {
        return $this->setProperty('transmissionMethod', $transmissionMethod);
    }

    /**
     * A medical test typically performed given this condition.
     *
     * @param \Municipio\Schema\Contracts\MedicalTestContract|\Municipio\Schema\Contracts\MedicalTestContract[] $typicalTest
     *
     * @return static
     *
     * @see https://schema.org/typicalTest
     * @see https://health-lifesci.schema.org
     */
    public function typicalTest($typicalTest)
    {
        return $this->setProperty('typicalTest', $typicalTest);
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
