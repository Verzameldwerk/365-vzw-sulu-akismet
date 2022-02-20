// @flow
import React from 'react';
import {computed, action, observable} from 'mobx';
import {observer} from 'mobx-react';
import {ResourceRequester} from 'sulu-admin-bundle/services';
import {translate} from 'sulu-admin-bundle/utils';
import {AbstractListToolbarAction} from 'sulu-admin-bundle/views';

class TriggerToolbarAction extends AbstractListToolbarAction {
    @observable loading: boolean = false;

    @computed get label(): string {
        const {label} = this.options;

        if (typeof label !== 'string' || !label) {
            throw new Error('The "label" option must be a non-empty string!');
        }

        return translate(label);
    }

    @computed get icon(): string {
        const {icon} = this.options;

        if (typeof icon !== 'string' || !icon) {
            throw new Error('The "icon" option must be a non-empty string!');
        }

        return icon;
    }

    @computed get action(): string {
        const {action} = this.options;

        if (typeof action !== 'string' || !action) {
            throw new Error('The "action" option must be a non-empty string!');
        }

        return action;
    }

    getToolbarItemConfig() {
        return {
            type: 'button',
            label: this.label,
            icon: this.icon,
            onClick: () => void this.handleClick(),
            disabled: this.listStore.selectionIds.length === 0,
            loading: this.loading,
        };
    }

    @action setLoading = (loading: boolean) => {
        this.loading = loading;
    }

    handleClick = async () => {
        this.setLoading(true);
        const promises = [];

        this.listStore.selectionIds.forEach(id => {
            promises.push(
                ResourceRequester.post(this.listStore.resourceKey, undefined, {id, action: this.action})
            );
        });

        await Promise.all(promises);
        this.listStore.reload();
        this.listStore.clearSelection();
        this.setLoading(false);
    }
}

export default TriggerToolbarAction;
