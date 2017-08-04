<?php

namespace Ampersand\Bundle\VariantGroupRestApiBundle\Controller;

use Pim\Component\Api\Exception\ViolationHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Pim\Component\Enrich\Converter\ConverterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Pim\Component\Catalog\Builder\ProductTemplateBuilderInterface;
use Pim\Component\Catalog\Factory\GroupFactory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VariantGroupController
{

    /** @var GroupRepositoryInterface */
    protected $repository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var SaverInterface */
    protected $saver;

    /** @var RemoverInterface */
    protected $remover;

    /** @var UserContext */
    protected $userContext;

    /** @var AttributeConverterInterface */
    protected $attributeConverter;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var NormalizerInterface */
    protected $violationNormalizer;

    /** @var CollectionFilterInterface */
    protected $variantGroupDataFilter;

    /** @var ConverterInterface */
    protected $productValueConverter;

    /** @var GroupFactory */
    protected $groupFactory;

    /** @var ProductTemplateBuilderInterface */
    protected $productTemplateBuilder;

    /** @var array */
    protected $apiConfiguration;

    /**
     * VariantGroupController constructor.
     * @param GroupRepositoryInterface $repository
     * @param NormalizerInterface $normalizer
     * @param ObjectUpdaterInterface $updater
     * @param SaverInterface $saver
     * @param RemoverInterface $remover
     * @param UserContext $userContext
     * @param AttributeConverterInterface $attributeConverter
     * @param ValidatorInterface $validator
     * @param NormalizerInterface $violationNormalizer
     * @param CollectionFilterInterface $variantGroupDataFilter
     * @param ConverterInterface $productValueConverter
     * @param GroupFactory $groupFactory
     * @param ProductTemplateBuilderInterface $productTemplateBuilder
     * @param array $apiConfiguration
     * @author Cristian Quiroz <cq@amp.co>
     */
    public function __construct(
        GroupRepositoryInterface $repository,
        NormalizerInterface $normalizer,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
        RemoverInterface $remover,
        UserContext $userContext,
        AttributeConverterInterface $attributeConverter,
        ValidatorInterface $validator,
        NormalizerInterface $violationNormalizer,
        CollectionFilterInterface $variantGroupDataFilter,
        ConverterInterface $productValueConverter,
        GroupFactory $groupFactory,
        ProductTemplateBuilderInterface $productTemplateBuilder,
        array $apiConfiguration
    ) {
        $this->repository = $repository;
        $this->normalizer = $normalizer;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->remover = $remover;
        $this->userContext = $userContext;
        $this->attributeConverter = $attributeConverter;
        $this->validator = $validator;
        $this->violationNormalizer = $violationNormalizer;
        $this->variantGroupDataFilter = $variantGroupDataFilter;
        $this->productValueConverter = $productValueConverter;
        $this->groupFactory = $groupFactory;
        $this->productTemplateBuilder = $productTemplateBuilder;
        $this->apiConfiguration = $apiConfiguration;
    }

    public function getAction(Request $request, $code)
    {
        $variantGroup = $this->repository->findOneByIdentifier($code);
        if (null === $variantGroup) {
            throw new NotFoundHttpException(sprintf('Variant group with code "%s" not found', $code));
        }

        return new JsonResponse(
            $this->normalizer->normalize(
                $variantGroup,
                'internal_api',
                $this->userContext->toArray() + ['with_variant_group_values' => true]
            )
        );
    }

    /**
     * @param Request $request
     *
     * @throws BadRequestHttpException
     *
     * @return Response
     */
    public function createAction(Request $request)
    {

        $data = $this->getDecodedContent($request->getContent());
        if (array_key_exists('code', $data) && array_key_exists('type', $data)) {
            $code = $data['code'];
            $type = $data['type'];
        } else {
            throw new BadRequestHttpException('Invalid json message received. You must include a code and type.');
        }

        $variantGroup = $this->repository->findOneByIdentifier($code);
        if (null === $variantGroup) {
            $variantGroup = $this->groupFactory->createGroup($type);
            $variantGroup->setCode($code);
            $variantGroup->setProductTemplate($this->productTemplateBuilder->createProductTemplate());
        }

        $data['values'] = $this->productValueConverter->convert($data['values']);

        $data = $this->convertLocalizedAttributes($data);
        $data = $this->variantGroupDataFilter->filterCollection($data, null);

        $this->updater->update($variantGroup, $data);

        $violations = $this->validator->validate($variantGroup);
        $violations->addAll($this->validator->validate($variantGroup->getProductTemplate()));
        $violations->addAll($this->attributeConverter->getViolations());

        if (0 < $violations->count()) {
            throw new ViolationHttpException($violations);
        }

        $this->saver->save($variantGroup, [
            'copy_values_to_products' => true
        ]);

        return new JsonResponse($this->normalizer->normalize(
            $variantGroup,
            'internal_api',
            $this->userContext->toArray() + ['with_variant_group_values' => true]
        ));
    }

    /**
     * Get the JSON decoded content. If the content is not a valid JSON, it throws an error 400.
     *
     * @param string $content content of a request to decode
     *
     * @throws BadRequestHttpException
     *
     * @return array
     */
    private function getDecodedContent($content)
    {
        $decodedContent = json_decode($content, true);

        if (null === $decodedContent) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $decodedContent;
    }

    /**
     * Convert localized attributes to the default format
     *
     * @param array $data
     *
     * @return array
     */
    private function convertLocalizedAttributes(array $data)
    {
        $locale = $this->userContext->getUiLocale()->getCode();
        $data['values'] = $this->attributeConverter->convertToDefaultFormats(
            $data['values'],
            ['locale' => $locale]
        );

        return $data;
    }
}
