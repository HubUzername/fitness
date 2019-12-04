<?php
    namespace App\Form;

    use App\Entity\User;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\Extension\Core\Type\EmailType;
    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    use Symfony\Component\Form\Extension\Core\Type\FileType;
    use Symfony\Component\HttpFoundation\File\Exception\FileException;
    use Symfony\Component\Validator\Constraints\File;
    use Symfony\Component\HttpFoundation\File\UploadedFile;

    class UserType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add("namelastname", TextType::class, ["required" => true])
                ->add("email", EmailType::class, ["required" => true])
                ->add("birthday", BirthdayType::class, ["required" => true, "format" => "dd-MM-yyyy"])
                ->add("phone", TextType::class, ["required" => true])
                ->add("sex", ChoiceType::class, ["required" => true, "choices" => ["Male" => 1, "Female" => 2], 'expanded' => true])
                ->add("updatedPhoto", FileType::class, [
                    "required" => false,
                    'constraints' => [
                        new File([
                            'maxSize' => '2M',
                            'mimeTypes' => [
                                'image/png',
                                'image/jpeg',
                                'image/gif',
                            ],
                            'mimeTypesMessage' => 'Пожалуйста загрузите фотографию формата .gif, .png или .jpeg',
                        ])
                    ]
                ])
            ;
        }

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults([
                'data_class' => User::class,
            ]);
        }
    }
