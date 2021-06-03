<?php global $view;

use Copper\Component\CP\CPController;
use Copper\Component\HTML\HTML;
use Copper\Handler\ArrayHandler;

$route_action_list = $view->get('$route_action_list');
$route_group = $view->get('$route_group');
$route_list = $view->get('$route_list');

$resource = $view->get('$resource');

?>

<script>
    let route_action_list = <?=$view->json($route_action_list)?>;
    let route_group = '<?=$route_group?>';
    let route_list = <?=$view->json($route_list)?>;

    function edit_routes() {
        document.getElementById('edit_routes_popup').classList.toggle('hidden');
        document.getElementById('path').focus();
        document.getElementById('path').setSelectionRange(1, 1);
    }

    function route_action_save() {
        console.log('route action save');
        route_action_list_update(route_list, function (response) {
            console.log(response);
        })
    }

    function route_action_cancel() {
        document.getElementById('edit_routes_popup').classList.add('hidden');
    }

    function route_action_list_update(list, callback) {
        let url = '<?=CPController::ACTION_ROUTE_LIST_UPDATE?>';

        let data = {
            "list": list
        }

        copper.requestHandler.postJSON(url, data, callback)
    }

    function route_add_request(action_with_params, method, path, name, callback) {
        let url = '<?=CPController::ACTION_ROUTE_ADD?>';

        let data = {
            "action_with_params": action_with_params,
            "method": method,
            "path": path,
            "name": name,
        }

        copper.requestHandler.postJSON(url, data, callback);
    }

    function route_action_add() {
        let method = document.getElementById('method').value;
        let action_with_params = document.getElementById('action').value;
        let path = document.getElementById('path').value;
        let action = action_with_params.split('(')[0];
        let name = action + '@' + path;
        let name_without_group = (method === 'POST') ? method.toLowerCase() + '@' + path : path;
        let group = document.getElementById('route_group').value.toLowerCase();

        route_add_request(action_with_params, method, path, name, function (result) {
            let li = document.createElement('li');
            li.innerHTML = result.li;

            document.getElementById('routes_list_container').append(li);

            route_list.push({
                action: action,
                action_with_params: action_with_params,
                method: method,
                name: name,
                name_without_group: name_without_group,
                path: path
            })
        })
    }

    function route_action_input__method() {
        route_action_input__path(document.getElementById('path'));
    }

    function getPathList(path) {
        let path_list = [];

        path.split('/').forEach(part => {
            if (part[0] === void 0 || part[0] === '{')
                return;
            let text = part[0].toUpperCase() + part.substr(1);
            path_list.push(text)
        });

        return path_list;
    }

    function craftRouteActionFunction(path, action, path_list) {
        let params = [];

        path_list = path_list || getPathList(path);

        Array.from(path.matchAll(/{(.*?)}/gm)).forEach(match => {
            params.push('$' + match[1]);
        })

        return action + path_list.join('') + '(' + params.join(', ') + ')';
    }

    function route_action_input__path(self, method_el, action_el, name) {
        method_el = method_el || document.getElementById('method');
        action_el = action_el || document.getElementById('action');

        self.value = self.value.toLowerCase();

        let the_path = self.value;

        if (self.value.indexOf('get@') >= 0) {
            self.value = self.value.replace(/\/get@|get@/g, '/');
            method_el.value = 'GET';
        }

        if (self.value.indexOf('post@') >= 0) {
            self.value = self.value.replace(/\/post@|post@/g, '/');
            method_el.value = 'POST';
        }

        self.value = self.value.replace(/[^A-Za-z0-9_/{}]/g, '');

        let path = self.value.replace(/_/g, '/');

        if (path[0] === '/')
            path = path.substr(1);

        let method = method_el.value.toLowerCase();
        let path_list = getPathList(path);

        action_el.value = craftRouteActionFunction(path, method, path_list);

        route_list.forEach((route, key) => {
            if (route.name === name) {
                route_list[key]['path'] = the_path;
                route_list[key]['method'] = method_el.value;
                route_list[key]['action_with_params'] = action_el.value;
                route_list[key]['action'] = action_el.value.split('(')[0];
            }
        })
    }

    function route_action_edit__method(self) {
        let name = self.name.split('[')[1].slice(0, -1);

        route_action_edit__path(document.querySelector('[name="path[' + name + ']"]'));
    }

    function route_action_edit__path(self) {
        console.log('editing existing path');

        let name = self.name.split('[')[1].slice(0, -1);
        let method = document.querySelector('[name="method[' + name + ']"]');
        let action = document.querySelector('[name="action[' + name + ']"]');

        route_action_input__path(self, method, action, name);
    }

    function route_action_edit__delete(self) {
        console.log('deleting existing action');
        let name = self.value.split('[')[1].slice(0, -1);

        route_list = copper.arrayHandler.assocDelete(route_list, {"name": name});
        document.getElementById(name).remove();

        console.log(route_list, name);
    }

    function route_action_input__action(self) {
        let action = self.value;

        console.log(action);
    }

    function route_action_select(self) {
        let action = self.value;

        let route = route_action_list.filter(x => x.name === action)[0];

        let parts = action.replace(/([a-z])([A-Z])/g, '$1 $2').split(' ');
        let method = parts[0].toUpperCase();

        parts.shift();

        let clean_parts = [];
        parts.forEach((part, key) => {
            clean_parts.push(part.toLowerCase());
        })

        if (route !== void 0)
            route.params.forEach(param => {
                clean_parts.push(`{${param.replace('$', '')}}`);
            })

        document.querySelector('#path').value = '/' + clean_parts.join('/');
        document.querySelector('#action').value = action;

        document.querySelector('#method').value = (action === '') ? 'GET' : method;
        document.getElementById('path').dispatchEvent(new Event('input'));
    }
</script>
<div id="edit_routes_popup" class="hidden">
    <h4><?= $resource ?></h4>
    <div class="close" onclick="route_action_cancel()">✖</div>
    <div class="routes_collection">
        <div class="info">
            <div class="group">Group: <?= HTML::input('group', $route_group)->id('route_group') ?></div>
            <div class="controller">
                Controller: <?= HTML::input('group', $resource::getControllerClassName())->disabled() ?></div>
        </div>
        <ul id="routes_list_container">
            <?php
            foreach ($route_list as $route) {
                $action = $route['action_with_params'];
                $method = $route['method'];
                $path = $route['path'];
                $name = $route['name'];

                $li = HTML::li()->id($name);

                $elMethod = HTML::select(['GET', 'POST'], "method[$name]", $method)->class('method')
                    ->onInput('route_action_edit__method(this)');

                $elPath = HTML::input("path[$name]", $path)->class('path')
                    ->onInput('route_action_edit__path(this)');

                $elAction = HTML::input("action[$name]", $action)->class('action')->disabled();

                $elDelete = HTML::button("Del", "del[$name]")->class('delete')
                    ->onClick('route_action_edit__delete(this)');;

                $li->addElement($elMethod)->addElement($elPath)->addElement($elAction)->addElement($elDelete);

                echo $li;
            }
            ?>
        </ul>
        <div class="update_controls">
            <button onclick="route_action_save()">Save Changes</button>
        </div>
    </div>
    <h5>Add New Route</h5>
    <div class="controls">
        <div>
            <?= HTML::select(['GET', 'POST'], "method")->idAsName()->onInput('route_action_input__method()') ?>
            <input type="text" id="path" class="path" oninput="route_action_input__path(this)"
                   placeholder="Enter path (e.g. /edit/product/{id} ) ..." value="/" autocomplete="off">
            <input type="text" id="action" class="action" oninput="route_action_input__action(this)"
                   placeholder="Controller action (e.g. postProduct) ... " disabled>
            <?= HTML::button("Add", "add")->onClick('route_action_add()') ?>
        </div>
        <div>
            <?php
            $action_list = ArrayHandler::assocFindStrict($route_action_list, ['used' => false]);
            echo HTML::selectCollection($action_list, "name", "name_with_params", "action_select")
                ->class('action_select')
                ->addInnerElementBefore(HTML::option('Select unused action from controller ...'))
                ->onChange('route_action_select(this)')
            ?>
        </div>
    </div>
</div>
