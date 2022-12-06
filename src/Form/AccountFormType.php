<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Account;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AccountFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('family', TextType::class)
            ->add('biography', TextareaType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Поле должно быть заполнено',
                    ]),
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Биогравфия должна содержать  {{ limit }}  символов',
                        // max length allowed by Symfony for security reasons
                        'max' => 1000,
                    ]),
                ],
            ])
            ->add('avatar_path', FileType::class, [
                'data_class' => null,
                'required' => false
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Account::class,
        ]);
    }

}