import DataMigrationBox from './Components/DataMigrationBox.jsx';

let {__} = wp.i18n;

jQuery(function () {

    let messages = {};

    ReactDOM.render(
        <DataMigrationBox nonce={ppmaSyncPostAuthor.nonce}
                          chunkSize={ppmaSyncPostAuthor.chunkSize}
                          actionGetInitialData={'get_sync_post_author_data'}
                          actionMigrationStep={'sync_post_author'}
                          actionFinishProcess={'finish_sync_post_author'}
                          buttonLabel={__('Update author field on posts', 'publishpress-authors')}
                          messageCollectingData={__('Collecting data...', 'publishpress-authors')}
                          messageEndingProcess={__('Finishing the process...', 'publishpress-authors')}
                          messageDone={__('Done! %d posts were updated.', 'publishpress-authors')}
                          messageWait={__('Please, wait...', 'publishpress-authors')}
                          messageStarting={__('Updating author field on posts...', 'publishpress-authors')}
                          messageProgress={__('Updated %d of %d posts...', 'publishpress-authors')}
        />,
        document.getElementById('publishpress-authors-sync-post-authors')
    );
});

