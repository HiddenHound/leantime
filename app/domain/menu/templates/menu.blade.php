@php
$settingsLink = $tpl->dispatchTplFilter(
    'settingsLink',
    $settingsLink,
    ['type' => $currentProjectType]
);
@endphp

@isset($_SESSION['currentProjectName'])

    @dispatchEvent('beforeMenu')

    <ul class="nav nav-tabs nav-stacked" id="expandedMenu">

        @dispatchEvent('afterMenuOpen')

        @if ($allAvailableProjects || !empty($_SESSION['currentProject']))

            <li class="project-selector">

                <div class="form-group">
                    <form action="" method="post">
                        <a
                            href="javascript:void(0);"
                            class="dropdown-toggle bigProjectSelector"
                            data-toggle="dropdown"
                        >
                            <span class="projectAvatar {{ $currentProjectType }}">
                                @switch($currentProjectType)
                                    @case('strategy')
                                    <span class="fa fa-chess"></span>
                                    @break

                                    @case('program')
                                    <span class="fa fa-layer-group"></span>
                                    @break

                                    @default
                                    <img src="{{ BASE_URL }}/api/projects?projectAvatar={{ $_SESSION['currentProject'] }}" />
                                    @break
                                @endswitch
                            </span>
                            {{ $_SESSION['currentProjectName'] }}&nbsp;<i class="fa fa-caret-right"></i>
                        </a>

                        @include('menu::submodules.projectSelector')
                    </form>
                </div>

            </li>

            <li class="dropdown scrollableMenu">

                <ul style="display:block;">
                    @foreach ($menuStructure as $key => $menuItem)
                        @switch ($menuItem['type'])
                            @case('header')
                                <li><a href="javascript:void(0);"><strong>{{ __($menuItem['title']) }}</strong></a></li>
                                @break

                            @case('separator')
                                <li class="separator"></li>
                                @break

                            @case('item')
                                <li
                                    @if (
                                        $module == $menuItem['module']
                                        && (!isset($menuItem['active']) || in_array($action, $menuItem['active']))
                                    )
                                        class="active"
                                    @endif
                                >
                                    <a href="{!! BASE_URL . $menuItem['href'] !!}">{{ __($menuItem['title']) }}</a>
                                </li>
                                @break
                        @endswitch
                    @endforeach

                    @if ($login::userIsAtLeast($roles::$manager))
                        <li class="fixedMenuPoint {{ $module == $settingsLink['module'] && $action == $settingsLink['action'] ? 'active' : '' }}">
                            <a href="{{ BASE_URL }}/{{ $settingsLink['module'] }}/{{ $settingsLink['action'] }}/{{ $_SESSION['currentProject'] }}">
                                {{ $settingsLink['label'] }}
                            </a>
                        </li>
                    @endif
                </ul>

            </li>

        @endif

        @dispatchEvent('beforeMenuClose')

    </ul>

    <ul class="nav nav-tabs nav-stacked" id="minimizedMenu">

        @dispatchEvent('afterMenuOpen')

        @if ($allAvailableProjects || $_SESSION['currentProject'] != '')

            <li class="project-selector">
                <div class="form-group">
                    <form action="" method="post">
                        <a
                            href="javascript:void(0)"
                            class="dropdown-toggle bigProjectSelector"
                            data-toggle="dropdown"
                            data-tippy-content="{{ $_SESSION['currentProjectName'] }}"
                            data-tippy-placement="right"
                        >
                            <span class="projectAvatar">
                                <img src="{{ BASE_URL }}/api/projects?projectAvatar={{ $_SESSION['currentProject'] }}" />
                            </span>

                            @include('menu::submodules.projectSelector')
                        </a>
                    </form>
                </div>
            </li>

            <li class="dropdown">
                <ul style="display: block;">
                    @foreach ($menuStructure as $key => $menuItem)
                        @switch ($menuItem['type'])
                            @case ('separator')
                                <li class="separator"></li>
                                @break

                            @case ('item')
                                <li
                                    @if (
                                        $module == $menuItem['module']
                                        && (!isset($menuItem['active']) || in_array($action, $menuItem['active']))
                                    )
                                        class="active"
                                    @endif
                                >
                                    <a
                                        href="{!! BASE_URL . $menuItem['href'] !!}"
                                        data-tippy-content="{{ __($menuItem['tooltip']) }}"
                                        data-tippy-placement="right"
                                    >
                                        <span class="{{ __($menuItem['icon']) }}"></span>
                                    </a>
                                </li>
                                @break

                            @case('submenu')
                                <ul style="display: block;" id="submenu-{{ $menuItem['id'] }}" class="submenu">
                                    @foreach($menuItem['submenu'] as $subkey => $submenuItem)
                                        @if ($submenuItem['type'] == 'item')
                                            <li
                                                @if (
                                                    $module == $submenuItem['module']
                                                    && (!isset($submenuItem['active']) || in_array($action, $submenuItem['active']))
                                                )
                                                    class="active"
                                                @endif
                                            >
                                                <a
                                                    href="{!! BASE_URL . $submenuItem['href'] !!}"
                                                    data-tippy-content="{{ __($submenuItem['tooltip']) }}"
                                                    data-tippy-placement="right"
                                                >
                                                    <span class="{{ __($submenuItem['icon']) }}"></span>
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                                @break
                        @endswitch
                    @endforeach
                </ul>
            </li>
        @endif

        @dispatchEvent('beforeMenuClose')

    </ul>

    @dispatchEvent('afterMenuClose')

@endisset

@once @push('scripts')
<script>
    jQuery('.projectSelectorTabs').tabs();

    let clientId = {{ !empty($currentClient) ? $currentClient : '-1' }};

    @php $childSelector = $projectHierarchy['program']['enabled'] ? 'program' : 'project'; @endphp

    @if ($projectHierarchy['program']['enabled'] || $projectHierarchy['strategy']['enabled'])
        @isset($_SESSION['submenuToggle']['strategy'])
            leantime.menuController.toggleHierarchy({{ "'{$_SESSION['submenuToggle']['strategy']}', '$childSelector' , 'strategy'" }});
        @endisset

        @if(isset($_SESSION['submenuToggle']['program']) && $projectHierarchy['program']['enabled'])
            leantime.menuController.toggleHierarchy({{ "'{$_SESSION['submenuToggle']['program']}', 'project', 'program'"}});
        @endif

        @foreach($projectHierarchy['project']['items'] as $key => $typeRow)
            @foreach($typeRow as $projectRow)
                @if($projectHierarchy['program']['enabled'] && $projectHierarchy['strategy']['enabled'])
                    @isset($_SESSION['submenuToggle']['program'], $_SESSION['submenuToggle']["clientDropdown-{$_SESSION['submenuToggle']['program']}-{$projectRow['clientId']}"])
                        leantime.menuController.toggleClientList({{ "'{$projectRow['clientId']}', '.clientIdHead-{$projectRow['clientId']} a', '{$_SESSION['submenuToggle']['clientDropdown-' . $_SESSION['submenuToggle']['program'] . '-' . $projectRow['clientId']]}'"}});
                    @endisset

                    @isset($_SESSION['submenuToggle']['strategy'], $_SESSION['submenuToggle']["clientDropdown-{$_SESSION['submenuToggle']['strategy']}-{$projectRow['clientId']}"])
                        leantime.menuController.toggleClientList({{ "'{$projectRow['clientId']}', '.clientIdHead-{$projectRow['clientId']} a', '{$_SESSION['submenuToggle']['clientDropdown-' . $_SESSION['submenuToggle']['strategy'] . '-' . $projectRow['clientId']]}'"}});
                    @endisset

                    @if(isset($_SESSION['submenuToggle']['clientDropdown--' . $projectRow['clientId']]) || $projectRow['clientId'] == $currentClient)
                        @php
                            $state = "closed";

                            if ($projectRow['clientId'] == $currentClient) {
                                $state = "open";
                            } else {
                                $state = $_SESSION['submenuToggle']['clientDropdown--' . $projectRow['clientId']];
                            }
                        @endphp

                        leantime.menuController.toggleClientList({{ "'{$projectRow['clientId']}', '.clientIdHead-{$projectRow['clientId']} a', '$state'"}});
                    @endif
                @endif
            @endforeach
        @endforeach
    @endif
</script>
@endpush @endonce
