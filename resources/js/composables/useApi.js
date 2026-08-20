import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

export function useApi() {
    const getDashboardStats = async () => {
        const { data } = await api.get('/dashboard/stats');
        return data;
    };

    const getPlatformDistribution = async () => {
        const { data } = await api.get('/dashboard/platform-distribution');
        return data;
    };

    const getRecentActivity = async () => {
        const { data } = await api.get('/dashboard/recent-activity');
        return data;
    };

    const getTopGames = async () => {
        const { data } = await api.get('/dashboard/top-games');
        return data;
    };

    const getActivity = async (params = {}) => {
        const { data } = await api.get('/activity', { params });
        return data;
    };

    const getActivityInsights = async (params = {}) => {
        const { data } = await api.get('/activity/insights', { params });
        return data;
    };

    const getGames = async (params = {}) => {
        const { data } = await api.get('/games', { params });
        return data;
    };

    const getGameDetail = async (id, params = {}) => {
        const { data } = await api.get(`/games/${id}/detail`, { params });
        return data;
    };

    const getLastSync = async () => {
        const { data } = await api.get('/sync/last');
        return data;
    };

    const triggerSync = async () => {
        const { data } = await api.post('/sync/trigger');
        return data;
    };

    return {
        api,
        getDashboardStats,
        getPlatformDistribution,
        getRecentActivity,
        getTopGames,
        getActivity,
        getActivityInsights,
        getGames,
        getGameDetail,
        getLastSync,
        triggerSync,
    };
}
