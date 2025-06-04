<?php

namespace App\Form;

use App\Entity\Repo;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\Regex;


class RepoWebType extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Repo::class,
            'blacklist' => [],
        ]);
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $blacklist = $options['blacklist'] ?? [];

        $builder
            ->add('name', TextType::class, [
                'label' => 'Repository name ',
                'attr' => [
                    'placeholder' => 'Enter repository name',
                ],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9._-]+$/',
                        'message' => 'Please use only alphanumeric characters, dots, dashes, and underscores',
                    ]),
                    new Regex([
                        'pattern' => '/^[^.]/',
                        'message' => 'Please do not start repository name with a dot',
                        // This regex also makes sure we do not try to operate on a directory called . or ..
                    ]),
                    new Callback(function ($name, ExecutionContextInterface $context) use ($blacklist) {
                        if (in_array(strtolower($name), array_map('strtolower', $blacklist))) {
                            $context->buildViolation('This repository name is not allowed.')
                                ->addViolation();
                        }
                    }),
                ],
            ])
            ->add('vcs', ChoiceType::class, [
                'choices' => [
                    'git' => Repo::VCS_GIT,
                    'riel' => Repo::VCS_RIEL,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('isPrivate', ChoiceType::class, [
                'label' => 'Privacy',
                'choices' => [
                    'Public' => false,
                    'Private' => true,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
        ;
    }
}
