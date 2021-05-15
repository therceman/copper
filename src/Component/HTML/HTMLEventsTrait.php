<?php


namespace Copper\Component\HTML;


trait HTMLEventsTrait
{
    /**
     * @param $event
     * @param null $value
     * @param array $args
     * @return $this
     */
    public function on($event, $value = null, $args = [])
    {
        // implement logic in parent class

        return $this;
    }

    /**
     * Occurs when the loading of an audio/video is aborted
     * <hr>
     * <code>
     * <audio>, <video>
     * </code>
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onAbort(string $value, $args = [])
    {
        return $this->on('abort', $value, $args);
    }

    /**
     * Occurs when a page has started printing, or if the print dialogue box has been closed.
     * <hr>
     * <code>
     * <body>
     * </code>
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onAfterprint(string $value, $args = [])
    {
        return $this->on('afterprint', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onAuxclick(string $value, $args = [])
    {
        return $this->on('auxclick', $value, $args);
    }

    /**
     * Occurs when a page is about to be printed (before the print dialogue box appears).
     * <hr>
     * <code>
     * <body>
     * </code>
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onBeforeprint(string $value, $args = [])
    {
        return $this->on('beforeprint', $value, $args);
    }

    /**
     * Occurs when the document is about to be unloaded.
     * <hr>
     * <code>
     * <body>
     * </code>
     * <hr>
     * <p>This event allows you to display a message in a confirmation dialog box to inform the user whether he/she wants to stay or leave the current page</p>
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onBeforeunload(string $value, $args = [])
    {
        return $this->on('beforeunload', $value, $args);
    }

    /**
     * Occurs when an object loses focus.
     * <hr>
     * <code>
     * ALL HTML elements, EXCEPT:
     * <base>,<bdo>,<br>,<head>,<html>,<iframe>,<meta>,<param>,<script>,<style>,<title>
     * </code>
     *
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onBlur(string $value, $args = [])
    {
        return $this->on('blur', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onCancel(string $value, $args = [])
    {
        return $this->on('cancel', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onCanplay(string $value, $args = [])
    {
        return $this->on('canplay', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onCanplaythrough(string $value, $args = [])
    {
        return $this->on('canplaythrough', $value, $args);
    }

    /**
     * An HTML element has been changed, for example: input field
     *
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onChange(string $value, $args = [])
    {
        return $this->on('change', $value, $args);
    }

    /**
     * The user clicks an HTML element
     *
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onClick(string $value, $args = [])
    {
        return $this->on('click', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onClose(string $value, $args = [])
    {
        return $this->on('close', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onContextmenu(string $value, $args = [])
    {
        return $this->on('ctextmenu', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onCopy(string $value, $args = [])
    {
        return $this->on('copy', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onCuechange(string $value, $args = [])
    {
        return $this->on('cuechange', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onCut(string $value, $args = [])
    {
        return $this->on('cut', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onDblclick(string $value, $args = [])
    {
        return $this->on('dblclick', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onDrag(string $value, $args = [])
    {
        return $this->on('drag', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onDragend(string $value, $args = [])
    {
        return $this->on('dragend', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onDragenter(string $value, $args = [])
    {
        return $this->on('dragenter', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onDragleave(string $value, $args = [])
    {
        return $this->on('dragleave', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onDragover(string $value, $args = [])
    {
        return $this->on('dragover', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onDragstart(string $value, $args = [])
    {
        return $this->on('dragstart', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onDrop(string $value, $args = [])
    {
        return $this->on('drop', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onDurationchange(string $value, $args = [])
    {
        return $this->on('duratichange', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onEmptied(string $value, $args = [])
    {
        return $this->on('emptied', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onEnded(string $value, $args = [])
    {
        return $this->on('ended', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onError(string $value, $args = [])
    {
        return $this->on('error', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onFocus(string $value, $args = [])
    {
        return $this->on('focus', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onFormdata(string $value, $args = [])
    {
        return $this->on('formdata', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onHashchange(string $value, $args = [])
    {
        return $this->on('hashchange', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onInput(string $value, $args = [])
    {
        return $this->on('input', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onInvalid(string $value, $args = [])
    {
        return $this->on('invalid', $value, $args);
    }

    /**
     * The user pushes a keyboard key
     *
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onKeydown(string $value, $args = [])
    {
        return $this->on('keydown', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onKeypress(string $value, $args = [])
    {
        return $this->on('keypress', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onKeyup(string $value, $args = [])
    {
        return $this->on('keyup', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onLanguagechange(string $value, $args = [])
    {
        return $this->on('languagechange', $value, $args);
    }

    /**
     * The browser has finished loading the page
     * <hr>
     * Supported Tags:
     * <code>
     * <body>, <frame>, <iframe>, <img>, <input type="image">, <link>, <script>, <style>
     * </code>
     *
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onLoad(string $value, $args = [])
    {
        return $this->on('load', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onLoadeddata(string $value, $args = [])
    {
        return $this->on('loadeddata', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onLoadedmetadata(string $value, $args = [])
    {
        return $this->on('loadedmetadata', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onLoadstart(string $value, $args = [])
    {
        return $this->on('loadstart', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onMessage(string $value, $args = [])
    {
        return $this->on('message', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onMessageerror(string $value, $args = [])
    {
        return $this->on('messageerror', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onMousedown(string $value, $args = [])
    {
        return $this->on('mousedown', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onMouseenter(string $value, $args = [])
    {
        return $this->on('mouseenter', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onMouseleave(string $value, $args = [])
    {
        return $this->on('mouseleave', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onMousemove(string $value, $args = [])
    {
        return $this->on('mousemove', $value, $args);
    }

    /**
     * The user moves the mouse away from an HTML element
     *
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onMouseout(string $value, $args = [])
    {
        return $this->on('mouseout', $value, $args);
    }

    /**
     * The user moves the mouse over an HTML element
     *
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onMouseover(string $value, $args = [])
    {
        return $this->on('mouseover', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onMouseup(string $value, $args = [])
    {
        return $this->on('mouseup', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onOffline(string $value, $args = [])
    {
        return $this->on('offline', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onOnline(string $value, $args = [])
    {
        return $this->on('online', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onPagehide(string $value, $args = [])
    {
        return $this->on('pagehide', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onPageshow(string $value, $args = [])
    {
        return $this->on('pageshow', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onPaste(string $value, $args = [])
    {
        return $this->on('paste', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onPause(string $value, $args = [])
    {
        return $this->on('pause', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onPlay(string $value, $args = [])
    {
        return $this->on('play', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onPlaying(string $value, $args = [])
    {
        return $this->on('playing', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onPopstate(string $value, $args = [])
    {
        return $this->on('popstate', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onProgress(string $value, $args = [])
    {
        return $this->on('progress', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onRatechange(string $value, $args = [])
    {
        return $this->on('ratechange', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onRejectionhandled(string $value, $args = [])
    {
        return $this->on('rejectihandled', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onReset(string $value, $args = [])
    {
        return $this->on('reset', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onResize(string $value, $args = [])
    {
        return $this->on('resize', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onScroll(string $value, $args = [])
    {
        return $this->on('scroll', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onSecuritypolicyviolation(string $value, $args = [])
    {
        return $this->on('securitypolicyviolati', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onSeeked(string $value, $args = [])
    {
        return $this->on('seeked', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onSeeking(string $value, $args = [])
    {
        return $this->on('seeking', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onSelect(string $value, $args = [])
    {
        return $this->on('select', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onSlotchange(string $value, $args = [])
    {
        return $this->on('slotchange', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onStalled(string $value, $args = [])
    {
        return $this->on('stalled', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onStorage(string $value, $args = [])
    {
        return $this->on('storage', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onSubmit(string $value, $args = [])
    {
        return $this->on('submit', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onSuspend(string $value, $args = [])
    {
        return $this->on('suspend', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onTimeupdate(string $value, $args = [])
    {
        return $this->on('timeupdate', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onToggle(string $value, $args = [])
    {
        return $this->on('toggle', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onUnhandledrejection(string $value, $args = [])
    {
        return $this->on('unhandledrejecti', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onUnload(string $value, $args = [])
    {
        return $this->on('unload', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onVolumechange(string $value, $args = [])
    {
        return $this->on('volumechange', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onWaiting(string $value, $args = [])
    {
        return $this->on('waiting', $value, $args);
    }

    /**
     * @param string $value
     * @param array $args
     * @return $this
     */
    public function onWheel(string $value, $args = [])
    {
        return $this->on('wheel', $value, $args);
    }
}