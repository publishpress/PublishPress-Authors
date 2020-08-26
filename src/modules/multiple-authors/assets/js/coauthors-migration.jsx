let {__} = wp.i18n;

class PPAuthorsCoAuthorsMigrationBox extends React.Component {
    constructor(props) {
        super(props);

        this.renderDeactivatePluginOption = this.renderDeactivatePluginOption.bind(this);
        this.renderProgressBar = this.renderProgressBar.bind(this);
        this.renderLog = this.renderLog.bind(this);
        this.deactivateCoAuthorsPlus = this.deactivateCoAuthorsPlus.bind(this);
        this.startMigration = this.startMigration.bind(this);
        this.clickStart = this.clickStart.bind(this);
        this.reset = this.reset.bind(this);
        this.migrateChunkOfData = this.migrateChunkOfData.bind(this);
        this.getCoAuthorsMigrationInitialData = this.getCoAuthorsMigrationInitialData.bind(this);

        this.state = {
            totalToMigrate: 0,
            totalMigrated: 0,
            inProgress: false,
            chunkSize: this.props.chunkSize,
            progress: 0,
            log: '',
            showDeactivateOption: false,
            disablingCoAuthors: false
        };
    }

    clickStart(e) {
        e.preventDefault();

        this.startMigration();
    }

    getCoAuthorsMigrationInitialData(next) {
        var self = this;

        this.setState({
            'log': __('Collecting data for the migration...', 'publishpress-authors')
        });

        window.setTimeout(() => {
            jQuery.ajax({
                type: 'GET',
                dataType: 'json',
                url: ajaxurl,
                async: false,
                data: {
                    action: 'get_coauthors_migration_data',
                    nonce: this.props.nonce
                },
                success: function (response) {
                    self.setState({
                        totalToMigrate: response.total
                    });

                    next();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    self.setState({
                        progress: 0,
                        inProgress: false,
                        log: __('Error: ', 'publishpress-authors') + errorThrown + ' [' + textStatus + ']',
                        showDeactivateOption: false
                    });
                }
            });
        }, 1000);
    }

    finishCoAuthorsMigration(onFinishCallBack) {
        var self = this;

        this.setState({
            progress: 99,
            'log': __('Creating missed post authors...', 'publishpress-authors')
        });

        window.setTimeout(() => {
            jQuery.ajax({
                type: 'GET',
                dataType: 'json',
                url: ajaxurl,
                async: false,
                data: {
                    action: 'finish_coauthors_migration',
                    nonce: this.props.nonce
                },
                success: function (response) {
                    onFinishCallBack();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    self.setState({
                        progress: 0,
                        inProgress: false,
                        log: __('Error: ', 'publishpress-authors') + errorThrown + ' [' + textStatus + ']',
                        showDeactivateOption: false
                    });
                }
            });
        }, 1000);
    }

    migrateChunkOfData() {
        var self = this;

        jQuery.ajax({
            type: 'GET',
            dataType: 'json',
            url: ajaxurl,
            data: {
                action: 'migrate_coauthors',
                nonce: this.props.nonce,
                chunkSize: this.state.chunkSize
            },
            success: function (response) {
                let totalMigrated = self.state.totalMigrated + self.state.chunkSize;

                if (totalMigrated > self.state.totalToMigrate) {
                    totalMigrated = self.state.totalToMigrate;
                }

                self.setState({
                    totalMigrated: totalMigrated,
                    progress: 2 + (Math.floor((98 / self.state.totalToMigrate) * totalMigrated))
                });

                if (totalMigrated < self.state.totalToMigrate) {
                    self.migrateChunkOfData();
                } else {
                    self.finishCoAuthorsMigration(function () {
                        self.setState({
                            progress: 100,
                            log: __('Done! Co-Authors Plus data was copied.', 'publishpress-authors'),
                            showDeactivateOption: true
                        });

                        window.setTimeout(() => {
                            self.setState({
                                inProgress: false
                            });
                        }, 1000);
                    });
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                self.setState({
                    progress: 0,
                    inProgress: false,
                    log: __('Error: ', 'publishpress-authors') + errorThrown + ' [' + textStatus + ']',
                    showDeactivateOption: false
                });
            }
        });
    }

    startMigration() {
        var self = this;

        this.setState(
            {
                progress: 1,
                inProgress: true,
                log: __('Please, wait...', 'publishpress-authors'),
                showDeactivateOption: false
            }
        );

        window.setTimeout(() => {
            self.getCoAuthorsMigrationInitialData(() => {
                self.setState(
                    {
                        progress: 2,
                        log: __('Copying authors\' data...', 'publishpress-authors')
                    }
                );

                self.migrateChunkOfData();
            });
        }, 1000);
    }

    deactivateCoAuthorsPlus() {
        var self = this;

        this.setState(
            {
                disablingCoAuthors: true,
                log: __('Deactivating Co-uthors Plus...', 'publishpress-authors')
            }
        );

        jQuery.ajax({
            type: 'GET',
            dataType: 'json',
            url: ajaxurl,
            data: {
                action: 'deactivate_coauthors_plus',
                nonce: this.props.nonce
            },
            success: function (response) {
                self.setState({
                    disablingCoAuthors: false,
                    log: __('Done! Co-Authors Plus is deactivated.', 'publishpress-authors'),
                    showDeactivateOption: false
                });
            },
            error: function (jqXHR, textStatus, errorThrown) {
                self.setState({
                    disablingCoAuthors: false,
                    log: __('Error: ', 'publishpress-authors') + errorThrown + ' [' + textStatus + ']'
                });
            }
        });
    }

    renderDeactivatePluginOption() {
        let label = __('Deactivate Co-Authors Plus', 'publishpress-authors');
        let isEnabled = !this.state.disablingCoAuthors;

        return (
            <PPAuthorsMaintenanceButton
                label={label}
                onClick={this.deactivateCoAuthorsPlus}
                enabled={isEnabled}/>
        );
    }

    reset() {
        this.setState({progress: 0, inProgress: false});
    }

    renderProgressBar() {
        return (
            <PPAuthorsProgressBar value={this.state.progress}/>
        );
    }

    renderLog() {
        return (
            <PPAuthorsMaintenanceLog log={this.state.log} show={this.state.showDeactivateOption}/>
        );
    }

    render() {
        let isEnabled = !this.state.inProgress;

        let progressBar = (this.state.inProgress) ? this.renderProgressBar() : '';
        let logPanel = (this.state.log != '') ? this.renderLog() : '';
        let deactivatePluginPanel = (this.state.showDeactivateOption) ? this.renderDeactivatePluginOption() : '';

        return (
            <div>
                <PPAuthorsMaintenanceButton
                    label={__('Copy Co-Authors Plus data', 'publishpress-authors')}
                    onClick={this.startMigration}
                    enabled={isEnabled}/>
                {deactivatePluginPanel}
                {progressBar}
                {logPanel}

            </div>
        );
    }
}

class PPAuthorsMaintenanceButton extends React.Component {
    constructor(props) {
        super(props);
    }

    render() {
        var disabled = !this.props.enabled;
        return (
            <input type="button"
                   className="button button-secondary button-danger ppma_maintenance_button"
                   onClick={this.props.onClick}
                   disabled={disabled}
                   value={this.props.label}/>
        );
    }
}

class PPAuthorsMaintenanceLog extends React.Component {
    constructor(props) {
        super(props);
    }

    render() {
        return (
            <div>
                <div class="ppma_maintenance_log" readOnly={true}>{this.props.log}</div>
            </div>
        );
    }
}

class PPAuthorsProgressBar extends React.Component {
    constructor(props) {
        super(props);
    }

    renderLabel() {
        return (
            <div className="p-progressbar-label">{this.props.value} %</div>
        );
    }

    render() {
        let className = 'p-progressbar p-component p-progressbar-determinate';
        let label = this.renderLabel();

        return (
            <div role="progressbar" id={this.props.id} className={className} style={this.props.style} aria-valuemin="0"
                 aria-valuenow={this.props.value} aria-valuemax="100" aria-label={this.props.value}>
                <div className="p-progressbar-value p-progressbar-value-animate"
                     style={{width: this.props.value + '%', display: 'block'}}></div>
                {label}
            </div>
        );
    }
}

jQuery(function () {
    ReactDOM.render(<PPAuthorsCoAuthorsMigrationBox notMigratedPostsId={ppmaCoAuthorsMigration.notMigratedPostsId}
                                                    nonce={ppmaCoAuthorsMigration.nonce}
                                                    chunkSize={5}/>,
        document.getElementById('publishpress-authors-coauthors-migration')
    );
});

