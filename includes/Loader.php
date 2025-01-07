<?php

namespace Supreme\ConditionalDiscounts;

/**
 * Loader class for managing the registration of actions and filters.
 *
 * This class acts as a central location to register all hooks (actions and filters)
 * for the plugin, ensuring separation of concerns and maintainability.
 */
class Loader
{
    /**
     * Array of actions to be registered with WordPress.
     *
     * @var array
     */
    private array $actions = [];

    /**
     * Array of filters to be registered with WordPress.
     *
     * @var array
     */
    private array $filters = [];

    /**
     * Registers an action hook.
     *
     * @param string   $hook          The name of the WordPress action hook.
     * @param callable $callback      The callback function to attach to the hook.
     * @param int      $priority      Priority at which the function should be fired.
     * @param int      $acceptedArgs  Number of arguments passed to the callback.
     */
    public function add_action(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        $this->actions[] = [
            'hook'          => $hook,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $acceptedArgs,
        ];
    }

    /**
     * Registers a filter hook.
     *
     * @param string   $hook          The name of the WordPress filter hook.
     * @param callable $callback      The callback function to attach to the hook.
     * @param int      $priority      Priority at which the function should be fired.
     * @param int      $acceptedArgs  Number of arguments passed to the callback.
     */
    public function add_filter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        $this->filters[] = [
            'hook'          => $hook,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $acceptedArgs,
        ];
    }

    /**
     * Registers all actions and filters with WordPress.
     */
    public function run(): void
    {
        foreach ($this->actions as $action) {
            add_action($action['hook'], $action['callback'], $action['priority'], $action['accepted_args']);
        }

        foreach ($this->filters as $filter) {
            add_filter($filter['hook'], $filter['callback'], $filter['priority'], $filter['accepted_args']);
        }
    }
}
