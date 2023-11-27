import { describe, expect, test } from '@jest/globals';
import { getApi } from '../../assets/js/vue/utils/axiosService';

describe('Axios Service Module', () => {
  test('Get Test', () => getApi('https://httpbin.org/get').then((res) => {
    expect(res.data.url).toBe('https://httpbin.org/get');
  }));
});
