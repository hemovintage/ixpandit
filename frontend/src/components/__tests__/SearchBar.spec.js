import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import SearchBar from '../SearchBar.vue'

describe('SearchBar', () => {
  beforeEach(() => {
    vi.useFakeTimers()
  })

  afterEach(() => {
    vi.useRealTimers()
  })

  it('disables the button when the query has fewer than 3 characters', async () => {
    const wrapper = mount(SearchBar)

    await wrapper.find('input').setValue('pi')

    expect(wrapper.find('button').element.disabled).toBe(true)
  })

  it('disables the button when the query contains invalid characters', async () => {
    const wrapper = mount(SearchBar)

    await wrapper.find('input').setValue('pi kachu!')

    expect(wrapper.find('button').element.disabled).toBe(true)
  })

  it('enables the button once the query has 3+ valid characters', async () => {
    const wrapper = mount(SearchBar)

    await wrapper.find('input').setValue('pik')

    expect(wrapper.find('button').element.disabled).toBe(false)
  })

  it('does not emit search before the debounce delay elapses', async () => {
    const wrapper = mount(SearchBar)

    await wrapper.find('input').setValue('pik')
    await vi.advanceTimersByTimeAsync(100)

    expect(wrapper.emitted('search')).toBeUndefined()
  })

  it('emits search after the debounce delay once the query becomes valid', async () => {
    const wrapper = mount(SearchBar)

    await wrapper.find('input').setValue('pik')
    await vi.advanceTimersByTimeAsync(300)

    expect(wrapper.emitted('search')[0]).toEqual(['pik'])
  })

  it('only searches once for the final value when the query changes rapidly', async () => {
    const wrapper = mount(SearchBar)

    await wrapper.find('input').setValue('pik')
    await vi.advanceTimersByTimeAsync(100)
    await wrapper.find('input').setValue('pika')
    await vi.advanceTimersByTimeAsync(300)

    expect(wrapper.emitted('search')).toHaveLength(1)
    expect(wrapper.emitted('search')[0]).toEqual(['pika'])
  })

  it('emits invalid when the query drops back below the minimum length', async () => {
    const wrapper = mount(SearchBar)

    await wrapper.find('input').setValue('pik')
    await vi.advanceTimersByTimeAsync(300)
    await wrapper.find('input').setValue('pi')

    expect(wrapper.emitted('invalid')).toHaveLength(1)
  })

  it('submits immediately without waiting for the debounce when the form is submitted', async () => {
    const wrapper = mount(SearchBar)

    await wrapper.find('input').setValue('pik')
    await wrapper.find('form').trigger('submit')

    expect(wrapper.emitted('search')[0]).toEqual(['pik'])
  })
})
