<?php

namespace App\Form\Regiment;

use App\Entity\Regiment\SectionPractice;
use App\Repository\Regiment\SectionPracticeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SectionPracticeType extends AbstractType
{
    public function __construct(
        private readonly SectionPracticeRepository $sectionPracticeRepository
    )
    {
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('dateTime', null, [
            'widget' => 'single_text',
        ]);
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var SectionPractice $sectionPractice */
            $sectionPractice = $event->getData();
            $form = $event->getForm();
            $obj = $this->sectionPracticeRepository->findOneBy([
                "section" => $sectionPractice->getSection(),
                "dateTime" => $sectionPractice->getDateTime()
            ]);
            if ($obj) {
                $form->get('dateTime')->addError(new FormError('Already exists'));
            }
        });
        // ->add('attendance')
        // ->add('section', EntityType::class, ['class' => Section::class, 'choice_label' => 'id'])
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SectionPractice::class,
        ]);
    }
}
