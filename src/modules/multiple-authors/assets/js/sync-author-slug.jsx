import DataMigrationBox from './Components/DataMigrationBox.jsx';

let {__} = wp.i18n;

jQuery(function () {

    let messages = {};

    ReactDOM.render(
        <DataMigrationBox nonce={ppmaSyncAuthorSlug.nonce}
                          chunkSize={ppmaSyncAuthorSlug.chunkSize}
                          actionGetInitialData={'get_sync_author_slug_data'}
                          actionMigrationStep={'sync_author_slug'}
                          actionFinishProcess={'finish_sync_author_slug'}
                          buttonLabel={__('Sync Author slugs to User logins', 'publishpress-authors')}
                          messageCollectingData={__('Collecting data...', 'publishpress-authors')}
                          messageEndingProcess={__('Finishing the process...', 'publishpress-authors')}
                          messageDone={__('Done! %d authors were updated.', 'publishpress-authors')}
                          messageWait={__('Please, wait...', 'publishpress-authors')}
                          messageStarting={__('Updating authors slug...', 'publishpress-authors')}
                          messageProgress={__('Updated %d of %d authors...', 'publishpress-authors')}
        />,
        document.getElementById('publishpress-authors-sync-author-slug')
    );
});

