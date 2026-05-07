<?php

namespace App\Traits\Filament\Specifics\Timeline;

use App\Traits\Filament\Specifics\GeneralData;

trait TimelineData
{
    use GeneralData;

    public static function actionOptionsAndColors()
    {
        return [
            'options' => [
                'create' => 'Create',
                'update' => 'Update',
                'delete' => 'Delete',
                'clone' => 'Clone',
                'validate' => 'Validate',
                'validateAny' => 'Validate Any',
                'deploy' => 'Deploy',
                'deployAny' => 'Deploy Any',
                'cancel' => 'Cancel',
                'cancelAny' => 'Cancel Any',
                'follow' => 'Follow',
                'refresh' => 'Refresh',
                'apply' => 'Apply',
                'applyAny' => 'Apply Any',
                'revoke' => 'Revoke',
                'revokeAny' => 'Revoke Any',
                'implement' => 'Implement',
                'implementAny' => 'Implement Any',
                'suspend' => 'Suspend',
                'suspendAny' => 'Suspend Any',
            ],
            'colors' => [
                'create' => 'success',
                'update' => 'info',
                'delete' => 'danger',
                'clone' => 'gray',
                'validate' => 'cyan',
                'validateAny' => 'cyan',
                'deploy' => 'teal',
                'deployAny' => 'teal',
                'cancel' => 'pink',
                'cancelAny' => 'pink',
                'follow' => 'sky',
                'refresh' => 'rose',
                'apply' => 'sky',
                'applyAny' => 'sky',
                'revoke' => 'pink',
                'revokeAny' => 'pink',
                'implement' => 'orange',
                'implementAny' => 'orange',
                'suspend' => 'warning',
                'suspendAny' => 'warning',
            ],
        ];
    }

    public static function actionDescriptions()
    {
        return [
            null => __('forms.timeline.descriptions.action'),
        ];
    }
}
