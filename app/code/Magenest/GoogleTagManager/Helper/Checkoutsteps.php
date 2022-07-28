<?php

namespace Magenest\GoogleTagManager\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Serialize;

class Checkoutsteps
{
    const CHECKOUT_PAGE_KEY = 'checkout_page';
    const STEP_NUMBER_KEY = 'step_number';

    /**
     * @var \Magento\Framework\Math\Random
     */
    private $mathRandom;

    /**
     * @var Serialize
     */
    private $serializer;

    public function __construct(
        \Magento\Framework\Math\Random $mathRandom,
        Serialize $serializer
    ) {
        $this->mathRandom = $mathRandom;
        $this->serializer = $serializer;
    }

    /**
     * Only allow integers greater than 0 as step numbers
     *
     * @param int $stepNumber
     * @return int
     * @throws LocalizedException
     */
    public function getValidStepNumber($stepNumber)
    {
        $stepNumber = (int)$stepNumber;

        if ($stepNumber <= 0) {
            throw new LocalizedException(\__('Only whole numbers greater than 0 are allowed.'));
        }

        return $stepNumber;
    }

    /**
     * Check if an array only has unique values
     *
     * @param mixed[] $values
     * @return bool
     */
    public function hasUniqueValues(array $values)
    {
        return \count($values) === \count(\array_unique($values));
    }

    /**
     * Generate a storable representation of a value
     *
     * @param string|int|bool|array $value
     * @return string
     * @throws LocalizedException
     */
    public function serializeValue($value)
    {
        $serializedValue = '';

        if (\is_array($value)) {
            $data = [];

            foreach ($value as $checkoutPage => $stepNumber) {
                $data[$checkoutPage] = $this->getValidStepNumber($stepNumber);
            }

            $serializedValue = $this->serializer->serialize($data); // phpcs:ignore Magento2.Security.InsecureFunction.DiscouragedWithAlternative
        }

        return $serializedValue;
    }

    /**
     * Return array from a serialized string
     *
     * @param string $value
     * @return array|mixed
     */
    public function unserializeValue($value)
    {
        return (\is_string($value) && !empty($value))
            ? $this->serializer->unserialize($value) // phpcs:ignore Magento2.Security.InsecureFunction.DiscouragedWithAlternative
            : [];
    }

    /**
     * Check if array fields are encoded
     *
     * @param mixed[] $values
     * @return bool
     */
    public function isEncodedArrayFieldValue(array $values)
    {
        $isEncoded = true;
        unset($values['__empty']);

        foreach ($values as $row) {
            if (!\is_array($row)
                || !\array_key_exists(self::CHECKOUT_PAGE_KEY, $row)
                || !\array_key_exists(self::STEP_NUMBER_KEY, $row)
            ) {
                $isEncoded = false;
                break 1;
            }
        }

        return $isEncoded;
    }

    /**
     * Encode array values to be used in \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param mixed[] $values
     * @return mixed[]
     * @throws LocalizedException
     */
    public function encodeArrayFieldValue(array $values)
    {
        $result = [];
        foreach ($values as $checkoutPage => $stepNumber) {
            $resultId = $this->mathRandom->getUniqueHash('_');
            $result[$resultId] = [
                self::CHECKOUT_PAGE_KEY => $checkoutPage,
                self::STEP_NUMBER_KEY => $this->getValidStepNumber($stepNumber),
            ];
        }

        return $result;
    }

    /**
     * Decode array values
     *
     * @param mixed[] $values
     * @return mixed[]
     * @throws LocalizedException
     */
    public function decodeArrayFieldValue(array $values)
    {
        $result = [];

        foreach ($values as $row) {
            if (!\is_array($row)) {
                continue;
            }

            if (!isset($row[self::CHECKOUT_PAGE_KEY], $row[self::STEP_NUMBER_KEY])) {
                continue;
            }

            $checkoutPage = $row[self::CHECKOUT_PAGE_KEY];
            $stepNumber = $this->getValidStepNumber($row[self::STEP_NUMBER_KEY]);
            $result[$checkoutPage] = $stepNumber;
        }

        return $result;
    }

    /**
     * Make value readable by \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param string|bool|int $value
     * @return mixed[]
     * @throws LocalizedException
     */
    public function makeArrayFieldValue($value)
    {
        $value = $this->unserializeValue($value);

        if (!$this->isEncodedArrayFieldValue($value)) {
            $value = $this->encodeArrayFieldValue($value);
        }

        return $value;
    }

    /**
     * Make value ready to be stored
     *
     * @param string|int|bool $value
     * @return mixed[]
     * @throws LocalizedException
     */
    public function makeStorableArrayFieldValue($value)
    {
        if ($this->isEncodedArrayFieldValue($value)) {
            $value = $this->decodeArrayFieldValue($value);
        }

        if (!$this->hasUniqueValues($value)) {
            throw new LocalizedException(\__('Each step number must be unique.'));
        }

        $value = $this->serializeValue($value);

        return $value;
    }
}
