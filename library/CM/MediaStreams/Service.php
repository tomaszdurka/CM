<?php

abstract class CM_MediaStreams_Service extends CM_Class_Abstract implements CM_Typed {

    /** @var CM_MediaStreams_StreamRepository */
    protected $_streamRepository;

    /**
     * @param CM_Model_Stream_Abstract $stream
     */
    abstract protected function _stopStream(CM_Model_Stream_Abstract $stream);

    /**
     * @param CM_MediaStreams_StreamRepository|null $streamRepository
     */
    public function __construct(CM_MediaStreams_StreamRepository $streamRepository = null) {
        $this->_streamRepository = $streamRepository;
    }

    public function checkStreams() {
        $streamRepository = $this->getStreamRepository();

        /** @var CM_Model_StreamChannel_Media $streamChannel */
        foreach ($streamRepository->getStreamChannels() as $streamChannel) {
            $streamChannelIsValid = $streamChannel->isValid();
            if ($streamChannel->hasStreamPublish()) {
                /** @var CM_Model_Stream_Publish $streamPublish */
                $streamPublish = $streamChannel->getStreamPublish();
                if (!$streamChannelIsValid) {
                    $this->_stopStream($streamPublish);
                } else {
                    if ($streamPublish->getAllowedUntil() < time()) {
                        $streamPublish->setAllowedUntil($streamChannel->canPublish($streamPublish->getUser(), $streamPublish->getAllowedUntil()));
                        if ($streamPublish->getAllowedUntil() < time()) {
                            $this->_stopStream($streamPublish);
                        }
                    }
                }
            }
            /** @var CM_Model_Stream_Subscribe $streamSubscribe */
            foreach ($streamChannel->getStreamSubscribes() as $streamSubscribe) {
                if (!$streamChannelIsValid) {
                    $this->_stopStream($streamSubscribe);
                } else {
                    if ($streamSubscribe->getAllowedUntil() < time()) {
                        $streamSubscribe->setAllowedUntil($streamChannel->canSubscribe($streamSubscribe->getUser(), $streamSubscribe->getAllowedUntil()));
                        if ($streamSubscribe->getAllowedUntil() < time()) {
                            $this->_stopStream($streamSubscribe);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param CM_MediaStreams_StreamRepository $streamRepository
     */
    public function setStreamRepository(CM_MediaStreams_StreamRepository $streamRepository) {
        $this->_streamRepository = $streamRepository;
    }

    /**
     * @return CM_MediaStreams_StreamRepository
     * @throws CM_Exception_Invalid
     */
    public function getStreamRepository() {
        if (null === $this->_streamRepository) {
            throw new CM_Exception_Invalid('Stream repository not set');
        }
        return $this->_streamRepository;
    }
}
