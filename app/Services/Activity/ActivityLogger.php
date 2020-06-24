<?php

namespace App\Services\Activity;

use App\Models\Activity;
use Illuminate\Auth\AuthManager;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    private $auth;

    protected $activity;

    protected $defaultLogName = "default";


    public function __construct(AuthManager $auth)
    {
        $this->auth = $auth;
    }

    public function causedBy($modelOrId)
    {
        if ($modelOrId === null) {
            return $this;
        }
        $model = $this->normalizeCauser($modelOrId);
        $this->getActivity()->causer()->associate($model);
        return $this;
    }


    public function withName(string $logName)
    {
        $this->getActivity()->log_name = $logName;
        return $this;
    }

    public function withProperties(array $properties)
    {
        $this->getActivity()->properties = collect($properties);
        return $this;
    }

    public function withProperty(string $key, $value)
    {
        $this->getActivity()->properties = $this->getActivity()->properties->put($key, $value);
        return $this;
    }

    public function log(string $text)
    {
        $activity = $this->activity;
        $activity->text = $text;
        $activity->save();
        $this->activity = null;
        return $activity;
    }

    public function performedOn(Model $model)
    {
        $this->getActivity()->source()->associate($model);
        return $this;
    }

    public function on(Model $model)
    {
        return $this->performedOn($model);
    }

    protected static function determineActivityModel(): string
    {
        $activityModel = Activity::class;
        if (! is_a($activityModel, Activity::class, true)
            || ! is_a($activityModel, Model::class, true)) {
            throw InvalidConfiguration::modelIsNotValid($activityModel);
        }

        return $activityModel;
    }

    protected static function getActivityModelInstance()
    {
        $activityModelClassName = self::determineActivityModel();
        return new $activityModelClassName();
    }

    protected function normalizeCauser($modelOrId): Model
    {
        if ($modelOrId instanceof Model) {
            return $modelOrId;
        }
        $guard = $this->auth->guard();
        $provider = method_exists($guard, 'getProvider') ? $guard->getProvider() : null;
        $model = method_exists($provider, 'retrieveById') ? $provider->retrieveById($modelOrId) : null;
        if ($model instanceof Model) {
            return $model;
        }
        throw new \Exception("Normalizer failed");
    }

    protected function getActivity()
    {
        if (! $this->activity instanceof Activity) {
            $this->activity = self::getActivityModelInstance();
            $this
                ->withName($this->defaultLogName)
                ->withProperties([])
                ->causedBy($this->auth->guard()->user());
        }
        return $this->activity;
    }
}
