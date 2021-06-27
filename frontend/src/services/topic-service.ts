import { AxiosError } from 'axios';
import { apiEndpoint } from '@/services/api';
import { Task } from '@/services/task-service';
import EndpointType from '@/types/Endpoint';

export interface Topic {
  id: string;
  title: string;
  description: string;
  activeTaskId: string;
  tasks?: Task[];
}

const API_TOPIC_ENDPOINT = apiEndpoint(EndpointType.TOPIC);

export const getTaskList = async (topicId: string): Promise<Task[]> => {
  const { data } = await API_TOPIC_ENDPOINT.get<Task[]>(
    `/${topicId}/${EndpointType.TASKS}`
  );
  return data;
};

export const getParticipantTasks = async (topicId: string): Promise<Task[]> => {
  try {
    const { data } = await API_TOPIC_ENDPOINT.get<Task[]>(
      `/${topicId}/${EndpointType.PARTICIPANT_TASKS}`
    );
    return data;
  } catch (error) {
    return (error as AxiosError).response?.data;
  }
};

export const postTask = async (
  taskId: string,
  data: Partial<Task>
): Promise<Task> => {
  const { data: responseData } = await API_TOPIC_ENDPOINT.post<Task>(
    `/${taskId}/${EndpointType.TASK}`,
    data
  );
  return responseData;
};